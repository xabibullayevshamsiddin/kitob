<?php

namespace App\Services\Shop;

use App\Models\CoinTransaction;
use App\Models\LiveQuestion;
use App\Models\PointTransaction;
use App\Models\Setting;
use App\Models\ShopItem;
use App\Models\ShopPurchase;
use App\Models\User;
use App\Models\UserEquipment;
use App\Models\UserItem;
use App\Models\UserProfile;
use App\Models\UserStreak;
use App\Services\Shop\Exceptions\ShopException;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ShopService
{
    /**
     * Tanga evaziga do'kondan buyum xarid qilish (Atomic, DB::transaction + lockForUpdate).
     *
     * @param User $user
     * @param ShopItem|string $item Buyum obyekti yoki uning 'key' kaliti
     * @param int $quantity Miqdori (standart: 1)
     * @param string|null $purchaseToken Ikki marta bosish (double-click/replay) dan himoya tokeni
     * @return array
     * @throws ShopException
     */
    public function purchase(User $user, ShopItem|string $item, int $quantity = 1, ?string $purchaseToken = null): array
    {
        if (!config('shop.enabled', true)) {
            throw new ShopException(trans('shop.shop_disabled'));
        }

        if ($quantity < 1) {
            throw new ShopException(trans('shop.invalid_quantity'));
        }

        $token = $purchaseToken ? trim($purchaseToken) : null;

        return DB::transaction(function () use ($user, $item, $quantity, $token) {
            // 1. Foydalanuvchi qatorini qulflab (lockForUpdate) qayta o'qish
            /** @var User $lockedUser */
            $lockedUser = User::where('id', $user->id)->lockForUpdate()->firstOrFail();

            // 2. Takroriy token tekshiruvi (Idempotency / Replay Protection)
            if ($token !== null && $token !== '') {
                $existingPurchase = ShopPurchase::where('purchase_token', $token)->first();
                if ($existingPurchase) {
                    return [
                        'success'     => true,
                        'duplicate'   => true,
                        'message'     => trans('shop.purchase_already_processed'),
                        'purchase_id' => $existingPurchase->id,
                        'item'        => $existingPurchase->shopItem,
                        'cost'        => $existingPurchase->price_paid,
                        'balance'     => $lockedUser->coin_balance,
                    ];
                }
            }

            // 3. Buyumni bazadan olish (mijoz yuborgan narxga aslo ishonilmaydi)
            /** @var ShopItem|null $shopItem */
            $shopItem = $this->resolveShopItem($item);

            if (!$shopItem) {
                throw new ShopException(trans('shop.item_not_found'));
            }

            // 4. Faollik va muddat oynasi tekshiruvi
            $now = now();
            if (!$shopItem->is_active) {
                throw new ShopException(trans('shop.item_not_available'));
            }
            if ($shopItem->available_from && $shopItem->available_from->isFuture()) {
                throw new ShopException(trans('shop.item_not_available'));
            }
            if ($shopItem->available_until && $shopItem->available_until->isPast()) {
                throw new ShopException(trans('shop.item_not_available'));
            }

            // 5. Egalik va max_quantity cheklovlarini tekshirish
            $userItem = UserItem::where('user_id', $lockedUser->id)
                ->where('shop_item_id', $shopItem->id)
                ->first();

            $isStreakFreeze = ($shopItem->key === 'streak_freeze')
                || (isset($shopItem->payload['effect']) && $shopItem->payload['effect'] === 'streak_freeze');

            $isCoinBooster = ($shopItem->key === 'coin_booster')
                || (isset($shopItem->payload['effect']) && $shopItem->payload['effect'] === 'coin_booster');

            $isAiQuestions = ($shopItem->key === 'ai_questions_pack')
                || (isset($shopItem->payload['effect']) && $shopItem->payload['effect'] === 'ai_questions');

            // Bir turdagi kuchaytirgich ustma-ust qo'yilmasin
            if ($isCoinBooster && $userItem && !$userItem->isExpired()) {
                throw new ShopException(trans('shop.booster_already_active'));
            }

            $isStackable = $shopItem->is_consumable || ($shopItem->max_quantity > 1);

            if (!$isStackable) {
                // Bir martalik buyum: agar foydalanuvchida bor bo'lsa va muddati o'tmagan bo'lsa
                if ($userItem && !$userItem->isExpired()) {
                    throw new ShopException(trans('shop.already_owned'));
                }
            } else {
                // Iste'mol qilinadigan yoki jamlanadigan buyum (consumable / stackable)
                if ($isStreakFreeze) {
                    $streak = UserStreak::firstOrCreate(
                        ['user_id' => $lockedUser->id],
                        ['current_streak' => 0, 'longest_streak' => 0, 'freeze_count' => 0]
                    );
                    $currentFreeze = (int) $streak->freeze_count;
                    $maxFreeze = (int) ($shopItem->max_quantity ?: 5);

                    if (($currentFreeze + $quantity) > $maxFreeze) {
                        throw new ShopException(trans('shop.streak_freeze_max_reached', ['max' => $maxFreeze]));
                    }
                } elseif ($isAiQuestions) {
                    $packSize = (int) ($shopItem->payload['amount'] ?? 10);
                    $currentPacks = $userItem ? (int) ceil($userItem->quantity / $packSize) : 0;
                    if (($currentPacks + $quantity) > $shopItem->max_quantity) {
                        throw new ShopException(trans('shop.max_quantity_reached', ['max' => $shopItem->max_quantity]));
                    }
                } else {
                    $currentQty = $userItem ? (int) $userItem->quantity : 0;
                    if (($currentQty + $quantity) > $shopItem->max_quantity) {
                        throw new ShopException(trans('shop.max_quantity_reached', ['max' => $shopItem->max_quantity]));
                    }
                }
            }

            // 6. Xarid narxini hisoblash va balansni tekshirish
            $totalCost = (int) $shopItem->price_coins * $quantity;
            if ($lockedUser->coin_balance < $totalCost) {
                throw new ShopException(trans('shop.insufficient_coins', [
                    'needed'  => $totalCost,
                    'balance' => $lockedUser->coin_balance,
                ]));
            }

            // 7. Balansni xavfsiz kamaytirish
            $lockedUser->coin_balance -= $totalCost;
            $lockedUser->save();

            // 8. Tanga tranzaksiyasi (CoinTransaction) ni yozish
            $coinTx = CoinTransaction::create([
                'user_id'        => $lockedUser->id,
                'coins'          => -$totalCost,
                'source'         => 'shop',
                'description'    => "Shop xaridi: {$shopItem->name_text}",
                'reference_type' => ShopItem::class,
                'reference_id'   => $shopItem->id,
                'balance_after'  => $lockedUser->coin_balance,
            ]);

            // 9. Xarid yozuvini (ShopPurchase) yaratish (purchase_token bilan)
            $finalToken = $token ?: Str::random(40);
            $purchase = ShopPurchase::create([
                'user_id'             => $lockedUser->id,
                'shop_item_id'        => $shopItem->id,
                'quantity'            => $quantity,
                'price_paid'          => $totalCost,
                'coin_transaction_id' => $coinTx->id,
                'purchase_token'      => $finalToken,
                'created_at'          => $now,
            ]);

            // 10. Inventar (user_items) va maxsus effektlarni yangilash
            $expiresAt = $shopItem->duration_minutes
                ? $now->copy()->addMinutes($shopItem->duration_minutes)
                : null;

            $qtyToAdd = $isAiQuestions
                ? (((int) ($shopItem->payload['amount'] ?? 10)) * $quantity)
                : $quantity;

            if ($userItem) {
                if ($isStackable) {
                    $userItem->quantity += $qtyToAdd;
                } else {
                    $userItem->quantity = 1;
                }
                $userItem->acquired_at = $now;
                $userItem->expires_at = $expiresAt;
                $userItem->save();
            } else {
                $userItem = UserItem::create([
                    'user_id'      => $lockedUser->id,
                    'shop_item_id' => $shopItem->id,
                    'quantity'     => $qtyToAdd,
                    'acquired_at'  => $now,
                    'expires_at'   => $expiresAt,
                ]);
            }

            // Agar Streak Freeze bo'lsa — user_streaks.freeze_count ni oshiramiz
            if ($isStreakFreeze) {
                $streak = $streak ?? UserStreak::firstOrCreate(
                    ['user_id' => $lockedUser->id],
                    ['current_streak' => 0, 'longest_streak' => 0, 'freeze_count' => 0]
                );
                $streak->increment('freeze_count', $quantity);
            }

            // 11. Orqaga moslik (Backward compatibility):
            // user_profiles.unlocked_items massivini yangilab turamiz
            $profile = $lockedUser->profile ?? UserProfile::firstOrCreate(['user_id' => $lockedUser->id]);
            if ($profile) {
                $profile->unlockItem($shopItem->key);
                $profile->save();
            }

            return [
                'success'     => true,
                'duplicate'   => false,
                'message'     => trans('shop.purchase_success', ['name' => $shopItem->name_text]),
                'purchase_id' => $purchase->id,
                'item'        => $shopItem,
                'cost'        => $totalCost,
                'balance'     => $lockedUser->coin_balance,
            ];
        });
    }

    /**
     * Sotib olingan buyumni jihozlash (Equip).
     *
     * @param User $user
     * @param ShopItem|string $item
     * @return array
     * @throws ShopException
     */
    public function equip(User $user, ShopItem|int|string $item): array
    {
        /** @var ShopItem|null $shopItem */
        $shopItem = $this->resolveShopItem($item);

        if (!$shopItem) {
            throw new ShopException(trans('shop.item_not_found'));
        }

        // Foydalanuvchi bu buyumga ega ekanini tekshirish
        $userItem = UserItem::where('user_id', $user->id)
            ->where('shop_item_id', $shopItem->id)
            ->first();

        if (!$userItem || $userItem->isExpired()) {
            throw new ShopException(trans('shop.not_owned'));
        }

        $profile = $user->profile ?? UserProfile::firstOrCreate(['user_id' => $user->id]);

        return DB::transaction(function () use ($user, $shopItem, $profile) {
            // Agar buyum to'plam (bundle) bo'lsa — bir nechta slotni birdan jihozlaydi
            if ($shopItem->category === 'bundle') {
                $slots = $shopItem->payload['bundle_slots'] ?? ['profile_theme', 'avatar_frame', 'chat_bubble'];
                foreach ($slots as $slot) {
                    UserEquipment::updateOrCreate(
                        ['user_id' => $user->id, 'slot' => $slot],
                        ['shop_item_id' => $shopItem->id]
                    );
                }

                // Keshlangan ustunlarni orqaga moslik uchun yangilaymiz
                $profile->profile_theme = $shopItem->key;
                $profile->avatar_frame = $shopItem->key;
                $profile->chat_bubble_theme = $shopItem->key;
                $profile->chat_style_preference = 'shop';
                $profile->save();
            } else {
                // Yagona slotli buyum
                $slot = $shopItem->slot;
                if ($slot) {
                    UserEquipment::updateOrCreate(
                        ['user_id' => $user->id, 'slot' => $slot],
                        ['shop_item_id' => $shopItem->id]
                    );

                    // Mos kesh ustunlarini yangilash
                    if ($slot === 'avatar_frame') {
                        $profile->avatar_frame = $shopItem->key;
                    } elseif ($slot === 'profile_theme') {
                        $profile->profile_theme = $shopItem->key;
                    } elseif ($slot === 'chat_bubble') {
                        $profile->chat_bubble_theme = $shopItem->key;
                    }
                    $profile->save();
                }
            }

            return [
                'success' => true,
                'message' => trans('shop.equip_success', ['name' => $shopItem->name_text]),
                'item'    => $shopItem,
            ];
        });
    }

    /**
     * Jihozlangan buyumni yechish (Unequip).
     *
     * @param User $user
     * @param string $slot Slot nomi yoki 'all'
     * @return array
     */
    public function unequip(User $user, string $slot = 'all'): array
    {
        $profile = $user->profile;

        return DB::transaction(function () use ($user, $slot, $profile) {
            if ($slot === 'all' || $slot === 'bundle' || $slot === 'profile_theme') {
                UserEquipment::where('user_id', $user->id)
                    ->whereIn('slot', ['profile_theme', 'avatar_frame', 'chat_bubble'])
                    ->delete();

                if ($profile) {
                    $profile->profile_theme = null;
                    $profile->avatar_frame = null;
                    $profile->chat_bubble_theme = null;
                    $profile->chat_style_preference = 'default';
                    $profile->save();
                }
            } else {
                UserEquipment::where('user_id', $user->id)
                    ->where('slot', $slot)
                    ->delete();

                if ($profile) {
                    if ($slot === 'avatar_frame') $profile->avatar_frame = null;
                    if ($slot === 'profile_theme') $profile->profile_theme = null;
                    if ($slot === 'chat_bubble') $profile->chat_bubble_theme = null;
                    $profile->save();
                }
            }

            return [
                'success' => true,
                'message' => trans('shop.unequip_success'),
            ];
        });
    }

    /**
     * Ball -> Tanga Almashtirish (Faqat bir tomonlama, 2-qaror qoidalari).
     *
     * @param User $user
     * @param int $points Almashtirilmoqchi bo'lgan ball miqdori
     * @return array
     * @throws ShopException
     */
    public function exchangePointsToCoins(User $user, int $points): array
    {
        if (!$this->isExchangeEnabled()) {
            throw new ShopException(trans('shop.exchange_disabled'));
        }

        $minPoints = $this->getExchangeMinPoints();
        if ($points < $minPoints) {
            throw new ShopException(trans('shop.exchange_min_points', ['min' => $minPoints]));
        }

        return DB::transaction(function () use ($user, $points) {
            /** @var User $lockedUser */
            $lockedUser = User::where('id', $user->id)->lockForUpdate()->firstOrFail();

            // 1. Mavjud ballarni tekshirish (total_points - points_spent)
            $available = $lockedUser->available_points;
            if ($points > $available) {
                throw new ShopException(trans('shop.insufficient_points'));
            }

            // 2. Kunlik limit tekshiruvi (faqat coin_transactions source=exchange asosida)
            $dailyLimit = $this->getExchangeDailyLimit();
            $rate = $this->getExchangeRate();
            $todayStart = Carbon::now('Asia/Tashkent')->startOfDay()->utc();

            $coinsExchangedToday = (int) CoinTransaction::where('user_id', $lockedUser->id)
                ->where('source', 'exchange')
                ->where('created_at', '>=', $todayStart)
                ->sum('coins');
            $pointsSpentToday = $coinsExchangedToday * $rate;

            // Agar bugungi limitdan oshsa:
            if (($pointsSpentToday + $points) > $dailyLimit) {
                throw new ShopException(trans('shop.exchange_daily_limit_reached', ['limit' => $dailyLimit]));
            }

            // 3. Almashtirish kursini hisoblash: standart 5 ball = 1 tanga
            $coinsToGive = (int) floor($points / $rate);
            $actualPointsDeducted = $coinsToGive * $rate;

            if ($coinsToGive < 1) {
                throw new ShopException(trans('shop.exchange_min_points', ['min' => $rate]));
            }

            // 4. points_spent va coin_balance ni oshirish
            // MUHIM: total_points va point_transactions ga aslo tegmaymiz (reytingga ta'sir qilmaydi)
            $lockedUser->points_spent += $actualPointsDeducted;
            $lockedUser->coin_balance += $coinsToGive;
            $lockedUser->save();

            // 5. Faqat CoinTransaction (source: exchange) yoziladi
            $coinTx = CoinTransaction::create([
                'user_id'       => $lockedUser->id,
                'coins'         => $coinsToGive,
                'source'        => 'exchange',
                'description'   => "Ballar almashtiruvi ({$actualPointsDeducted} ball evaziga)",
                'balance_after' => $lockedUser->coin_balance,
            ]);

            return [
                'success'               => true,
                'points_exchanged'      => $actualPointsDeducted,
                'coins_received'        => $coinsToGive,
                'new_coin_balance'      => $lockedUser->coin_balance,
                'remaining_points'      => $lockedUser->available_points,
                'message'               => trans('shop.exchange_success', [
                    'points' => $actualPointsDeducted,
                    'coins'  => $coinsToGive,
                ]),
            ];
        });
    }

    /**
     * Foydalanuvchi ma'lum buyumga egaligini tekshirish.
     */
    public function hasPurchased(User $user, ShopItem|int|string $item): bool
    {
        $shopItem = $this->resolveShopItem($item);
        if (!$shopItem) return false;

        $userItem = UserItem::where('user_id', $user->id)
            ->where('shop_item_id', $shopItem->id)
            ->first();

        return $userItem && !$userItem->isExpired();
    }

    /**
     * Buyumni ShopItem obyekti, ID yoki unikal key orqali topish.
     */
    public function resolveShopItem(ShopItem|int|string $item): ?ShopItem
    {
        if ($item instanceof ShopItem) {
            return $item;
        }

        if (is_numeric($item)) {
            return ShopItem::find((int) $item);
        }

        return ShopItem::where('key', trim((string) $item))->first();
    }

    /**
     * Foydalanuvchining ayni paytdagi jihozlarini olish.
     */
    public function getEquippedItems(User $user): array
    {
        return UserEquipment::with('shopItem')
            ->where('user_id', $user->id)
            ->get()
            ->keyBy('slot')
            ->toArray();
    }

    /**
     * Foydalanuvchining barcha inventarini olish.
     */
    public function getUserInventory(User $user)
    {
        return UserItem::with('shopItem')
            ->where('user_id', $user->id)
            ->where(function ($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })
            ->get();
    }

    /**
     * Payload ichidagi ranglarni qat'iy tekshirish (CSS injection / XSS oldini olish).
     */
    public static function validateHexColor(?string $color): bool
    {
        if ($color === null) return true;
        return (bool) preg_match('/^#[0-9A-Fa-f]{6}$/', trim($color));
    }

    /**
     * Almashtirish kursini olish (dinamik sozlamalar bilan).
     */
    public function getExchangeRate(): int
    {
        return max(1, (int) Setting::get('shop_exchange_rate', config('shop.exchange.rate_points_per_coin', 5)));
    }

    /**
     * Minimal almashtirish ballini olish.
     */
    public function getExchangeMinPoints(): int
    {
        return (int) Setting::get('shop_exchange_min_points', config('shop.exchange.min_points', 50));
    }

    /**
     * Kunlik almashtirish limitini olish.
     */
    public function getExchangeDailyLimit(): int
    {
        return (int) Setting::get('shop_exchange_daily_limit', config('shop.exchange.max_points_per_day', 250));
    }

    /**
     * Almashtirish faol yoki nofaolligi.
     */
    public function isExchangeEnabled(): bool
    {
        return (bool) Setting::get('shop_exchange_enabled', config('shop.exchange.enabled', true));
    }

    /**
     * AI savollar paketi: foydalanuvchidan 1 ta AI savolini sarflash.
     */
    public function consumeAiQuestion(User $user): bool
    {
        $item = ShopItem::where('key', 'ai_questions_pack')->first();
        if (!$item) {
            return false;
        }

        $userItem = UserItem::where('user_id', $user->id)
            ->where('shop_item_id', $item->id)
            ->first();

        if (!$userItem || $userItem->quantity <= 0) {
            return false;
        }

        if ($userItem->quantity <= 1) {
            $userItem->delete();
        } else {
            $userItem->decrement('quantity', 1);
        }

        return true;
    }

    /**
     * Viktorinani qayta topshirish bileti: 1 ta biletni sarflash.
     */
    public function consumeQuizRetryTicket(User $user): bool
    {
        $item = ShopItem::where('key', 'quiz_retry_ticket')->first();
        if (!$item) {
            return false;
        }

        $userItem = UserItem::where('user_id', $user->id)
            ->where('shop_item_id', $item->id)
            ->first();

        if (!$userItem || $userItem->quantity <= 0) {
            return false;
        }

        if ($userItem->quantity <= 1) {
            $userItem->delete();
        } else {
            $userItem->decrement('quantity', 1);
        }

        return true;
    }

    /**
     * Foydalanuvchi sotib olgan qo'shimcha guruh o'rinlari sonini qaytaradi.
     */
    public function getExtraGroupSlots(User $user): int
    {
        $item = ShopItem::where('key', 'extra_group_slot')->first();
        if (!$item) {
            return 0;
        }

        $userItem = UserItem::where('user_id', $user->id)
            ->where('shop_item_id', $item->id)
            ->first();

        if (!$userItem || $userItem->isExpired()) {
            return 0;
        }

        $perItemAmount = (int) ($item->payload['amount'] ?? 1);
        return (int) $userItem->quantity * $perItemAmount;
    }

    /**
     * Faol tanga kuchaytirgich (Coin Booster) koeffitsientini qaytaradi (faqat tanga uchun, ball EMAS).
     */
    public function getActiveCoinBoosterMultiplier(User $user): float
    {
        $item = ShopItem::where('key', 'coin_booster_60')
            ->orWhere('key', 'coin_booster')
            ->orWhere('payload->effect', 'coin_booster')
            ->first();
        if (!$item) {
            return 1.0;
        }

        $userItem = UserItem::where('user_id', $user->id)
            ->where('shop_item_id', $item->id)
            ->first();

        if (!$userItem) {
            return 1.0;
        }

        if ($userItem->isExpired()) {
            $userItem->delete();
            return 1.0;
        }

        return (float) ($item->payload['multiplier'] ?? 2.0);
    }

    /**
     * Foydalanuvchida faol tanga kuchaytirgich mavjudligi.
     */
    public function hasActiveBooster(User $user): bool
    {
        return $this->getActiveCoinBoosterMultiplier($user) > 1.0;
    }


    /**
     * Foydalanuvchida qancha ustuvor savol chiptasi borligini olish.
     */
    public function getLivePriorityTicketCount(User $user): int
    {
        $item = ShopItem::where('key', 'live_priority_question')->first();
        if (!$item) {
            return 0;
        }

        $userItem = UserItem::where('user_id', $user->id)
            ->where('shop_item_id', $item->id)
            ->first();

        if (!$userItem || $userItem->quantity <= 0 || $userItem->isExpired()) {
            return 0;
        }

        return (int) $userItem->quantity;
    }

    /**
     * Foydalanuvchi ushbu efirda qancha ustuvor savol berganini hisoblash.
     */
    public function getLivePriorityQuestionsUsed(User $user, int $liveEventId): int
    {
        return LiveQuestion::where('live_event_id', $liveEventId)
            ->where('user_id', $user->id)
            ->where('is_priority', true)
            ->count();
    }

    /**
     * Foydalanuvchi ushbu efirda yana ustuvor savol bera oladimi?
     */
    public function canSubmitLivePriorityQuestion(User $user, int $liveEventId): bool
    {
        $maxPerLive = (int) config('shop.max_priority_questions_per_live', 2);
        $used = $this->getLivePriorityQuestionsUsed($user, $liveEventId);
        if ($used >= $maxPerLive) {
            return false;
        }

        return $this->getLivePriorityTicketCount($user) > 0;
    }

    /**
     * Jonli efirda ustuvor savol chiptasini sarflash.
     */
    public function consumeLivePriorityQuestion(User $user): bool
    {
        $item = ShopItem::where('key', 'live_priority_question')->first();
        if (!$item) {
            return false;
        }

        $userItem = UserItem::where('user_id', $user->id)
            ->where('shop_item_id', $item->id)
            ->lockForUpdate()
            ->first();

        if (!$userItem || $userItem->quantity <= 0 || $userItem->isExpired()) {
            return false;
        }

        if ($userItem->quantity <= 1) {
            $userItem->delete();
        } else {
            $userItem->decrement('quantity', 1);
        }

        return true;
    }

    /**
     * Jonli efir savolini yaratish (ustuvor bo'lsa chiptani atomik sarflaydi).
     *
     * @throws ShopException
     */
    public function createLiveQuestion(User $user, int $liveEventId, string $questionText, bool $isPriority = false): LiveQuestion
    {
        $questionText = trim($questionText);

        if (!$isPriority) {
            return LiveQuestion::create([
                'live_event_id' => $liveEventId,
                'user_id'       => $user->id,
                'question'      => $questionText,
                'is_priority'   => false,
                'is_selected'   => false,
                'is_answered'   => false,
            ]);
        }

        return DB::transaction(function () use ($user, $liveEventId, $questionText) {
            $maxLimit = (int) config('shop.max_priority_questions_per_live', 2);
            $usedCount = LiveQuestion::where('live_event_id', $liveEventId)
                ->where('user_id', $user->id)
                ->where('is_priority', true)
                ->lockForUpdate()
                ->count();

            if ($usedCount >= $maxLimit) {
                throw new ShopException("Ushbu jonli efirda ko'pi bilan {$maxLimit} ta ustuvor savol berishingiz mumkin.");
            }

            $consumed = $this->consumeLivePriorityQuestion($user);
            if (!$consumed) {
                throw new ShopException("Sizda ustuvor savol chiptasi mavjud emas yoki muddati tugagan.");
            }

            return LiveQuestion::create([
                'live_event_id' => $liveEventId,
                'user_id'       => $user->id,
                'question'      => $questionText,
                'is_priority'   => true,
                'is_selected'   => false,
                'is_answered'   => false,
            ]);
        });
    }

    /**
     * Admin amali: Buyumni foydalanuvchidan qaytarib olish va tangasini qaytarish (Refund).
     *
     * @param User $user
     * @param ShopItem|int|string $item
     * @param int|null $quantity Agar null bo'lsa, to'liq qaytariladi
     * @param string|null $reason Qaytarish sababi (audit log uchun)
     * @return array
     * @throws ShopException
     */
    public function refund(User $user, ShopItem|int|string $item, ?int $quantity = null, ?string $reason = null): array
    {
        $shopItem = $this->resolveShopItem($item);
        if (!$shopItem) {
            throw new ShopException("Buyum topilmadi.");
        }

        return DB::transaction(function () use ($user, $shopItem, $quantity, $reason) {
            /** @var User $lockedUser */
            $lockedUser = User::where('id', $user->id)->lockForUpdate()->firstOrFail();

            $userItem = UserItem::where('user_id', $lockedUser->id)
                ->where('shop_item_id', $shopItem->id)
                ->first();

            $isStreakFreeze = ($shopItem->key === 'streak_freeze')
                || (isset($shopItem->payload['effect']) && $shopItem->payload['effect'] === 'streak_freeze');

            if (!$userItem && !$isStreakFreeze) {
                throw new ShopException(trans('shop.refund_not_owned'));
            }

            $currentQty = $userItem ? (int) $userItem->quantity : 0;
            if ($isStreakFreeze) {
                $streak = UserStreak::where('user_id', $lockedUser->id)->first();
                $currentQty = (int) ($streak?->freeze_count ?? 0);
            }

            if ($currentQty <= 0) {
                throw new ShopException(trans('shop.refund_not_owned'));
            }

            $qtyToRefund = $quantity !== null ? min($quantity, $currentQty) : $currentQty;
            $refundAmount = (int) $shopItem->price_coins * $qtyToRefund;

            // 1. Inventardan kamaytirish yoki o'chirish
            if ($userItem) {
                if ($userItem->quantity <= $qtyToRefund) {
                    $userItem->delete();
                } else {
                    $userItem->decrement('quantity', $qtyToRefund);
                }
            }

            if ($isStreakFreeze && isset($streak) && $streak) {
                $streak->decrement('freeze_count', $qtyToRefund);
            }

            // 2. Agar kiyilgan bo'lsa — yechish (unequip)
            UserEquipment::where('user_id', $lockedUser->id)
                ->where('shop_item_id', $shopItem->id)
                ->delete();

            $profile = $lockedUser->profile;
            if ($profile) {
                if ($shopItem->slot) {
                    $frameVal = $shopItem->payload['frame'] ?? $shopItem->key;
                    if ($shopItem->slot === 'avatar_frame' && $profile->avatar_frame === $frameVal) {
                        $profile->avatar_frame = null;
                    }
                    if ($shopItem->slot === 'profile_theme' && $profile->profile_theme === $shopItem->key) {
                        $profile->profile_theme = null;
                    }
                    if ($shopItem->slot === 'chat_bubble' && $profile->chat_bubble_theme === $shopItem->key) {
                        $profile->chat_bubble_theme = null;
                    }
                }
                $stillHas = UserItem::where('user_id', $lockedUser->id)->where('shop_item_id', $shopItem->id)->exists();
                if (!$stillHas) {
                    $unlocked = (array) ($profile->unlocked_items ?? []);
                    $profile->unlocked_items = array_values(array_filter($unlocked, fn ($k) => $k !== $shopItem->key));
                }
                $profile->save();
            }

            // 3. Tangalarni qaytarish (refund)
            $lockedUser->coin_balance += $refundAmount;
            $lockedUser->save();

            // 4. CoinTransaction (source: refund) yozish
            $coinTx = CoinTransaction::create([
                'user_id'        => $lockedUser->id,
                'coins'          => $refundAmount,
                'source'         => 'refund',
                'description'    => "Shop refund: {$shopItem->name_text}" . ($reason ? " ({$reason})" : ''),
                'reference_type' => ShopItem::class,
                'reference_id'   => $shopItem->id,
                'balance_after'  => $lockedUser->coin_balance,
            ]);

            // 5. Audit log
            Log::info("Admin shop refund executed", [
                'admin_id'      => auth()->id(),
                'user_id'       => $lockedUser->id,
                'shop_item_id'  => $shopItem->id,
                'shop_item_key' => $shopItem->key,
                'quantity'      => $qtyToRefund,
                'coins_refund'  => $refundAmount,
                'reason'        => $reason,
            ]);

            return [
                'success'       => true,
                'message'       => trans('shop.refund_success'),
                'refund_coins'  => $refundAmount,
                'refund_qty'    => $qtyToRefund,
                'balance_after' => $lockedUser->coin_balance,
            ];
        });
    }
}
