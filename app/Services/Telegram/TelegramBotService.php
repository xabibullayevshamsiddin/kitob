<?php

namespace App\Services\Telegram;

use App\Models\DailyQuote;
use App\Models\User;
use App\Support\PhoneNumber;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class TelegramBotService
{
    protected ?TelegramClient $client;

    /**
     * @param TelegramClient|null $client
     */
    public function __construct($client = null)
    {
        $this->client = $client instanceof TelegramClient ? $client : new TelegramClient();
    }

    /**
     * Telegram'dan kelgan barcha yangilanishlarni (updates) qayta ishlash.
     */
    public function handleUpdate(array $update): void
    {
        // 1. Agar pre_checkout_query (Stars to'lovidan oldingi tekshiruv) bo'lsa
        if (isset($update['pre_checkout_query'])) {
            $paymentService = new TelegramPaymentService($this->client);
            $paymentService->handlePreCheckoutQuery($update['pre_checkout_query']);
            return;
        }

        // 2. Agar Callback query (tugma bosilishi) bo'lsa
        if (isset($update['callback_query'])) {
            $this->handleCallbackQuery($update['callback_query']);
            return;
        }

        // 3. Agar oddiy xabar bo'lsa
        if (isset($update['message'])) {
            $this->handleMessage($update['message']);
            return;
        }
    }

    /**
     * Foydalanuvchidan kelgan xabarni qayta ishlash.
     */
    protected function handleMessage(array $message): void
    {
        $chatId = $message['chat']['id'] ?? null;
        $from = $message['from'] ?? [];
        $userId = $from['id'] ?? null;

        if (!$chatId || !$userId) {
            return;
        }

        // Agar foydalanuvchi muvaffaqiyatli Stars to'lovini amalga oshirgan bo'lsa
        if (isset($message['successful_payment'])) {
            $paymentService = new TelegramPaymentService($this->client);
            $paymentService->handleSuccessfulPayment($message);
            return;
        }

        // Agar foydalanuvchi o'z kontaktini ulashgan bo'lsa (Telefon raqamni tasdiqlash)
        if (isset($message['contact'])) {
            $this->handleContact($chatId, $userId, $message['contact'], $from);
            return;
        }

        $text = trim($message['text'] ?? '');
        if ($text === '') {
            return;
        }

        // Buyruqlarni ajratib olish (/start payload yoki /start@bot_username payload)
        $parts = explode(' ', $text, 2);
        $firstWord = strtolower($parts[0]);
        if (str_contains($firstWord, '@')) {
            $firstWord = explode('@', $firstWord)[0];
        }
        $payload = trim($parts[1] ?? '');

        // Matnni tozalash (emojilarni olib tashlab, harflar va raqamlarni solishtirish uchun)
        $clean = mb_strtolower(preg_replace('/[^\p{L}\p{N}\s\/]/u', '', $text));
        $clean = trim(preg_replace('/\s+/', ' ', $clean));

        // 1. /start buyrug'i
        if ($firstWord === '/start') {
            $this->handleStartCommand($chatId, $from, $payload);
            return;
        }

        // 2. Asosiy menyu / Buyruqlar
        if (in_array($firstWord, ['/menu', '/menyu', '/commands', '/buyruqlar'])
            || in_array($clean, ['menu', 'menyu', 'buyruqlar', 'asosiy menyu', 'bosh menyu'])) {
            $this->handleMenuCommand($chatId);
            return;
        }

        // 2.1. Menyuni yopish (klaviaturani yashirish) yoki Bekor qilish
        if (in_array($firstWord, ['/close', '/yopish'])
            || in_array($clean, ['menyuni yopish', 'yopish', 'close', 'menyu yopish', 'bekor qilish', 'bekor', 'cancel'])) {
            Cache::forget("tg_reg_token_{$userId}");
            Cache::forget("tg_reg_prompt_just_sent_{$userId}");
            Cache::forget("tg_user_verifying_{$userId}");
            Cache::forget("tg_verify_prompt_just_sent_{$userId}");
            $this->handleCloseMenuCommand($chatId);
            return;
        }

        // 3. Profil
        if (in_array($firstWord, ['/profil', '/profile'])
            || in_array($clean, ['profilim', 'profil', 'profilimni korish', 'shaxsiy profil'])) {
            $this->handleProfileCommand($chatId, $userId);
            return;
        }

        // 4. Streak
        if ($firstWord === '/streak'
            || in_array($clean, ['streak', 'streak holati', 'kunlik streak', 'zanjir'])) {
            $this->handleStreakCommand($chatId, $userId);
            return;
        }

        // 5. Balans / Tangalar
        if (in_array($firstWord, ['/balance', '/balans', '/coins', '/tangalar'])
            || in_array($clean, ['balans', 'tanga balansi', 'tangalar', 'hisobim'])) {
            $this->handleBalanceCommand($chatId, $userId);
            return;
        }

        // 6. Kun hikmati
        if (in_array($firstWord, ['/hikmat', '/quote'])
            || in_array($clean, ['kun hikmati', 'hikmat', 'iqtibos', 'kun iqtibosi'])) {
            $this->handleHikmatCommand($chatId);
            return;
        }

        // 7. Kitoblar / Mutolaa
        if (in_array($firstWord, ['/books', '/kitoblar', '/catalog'])
            || in_array($clean, ['kitoblar', 'kitoblar katalogi', 'mutolaa', 'kitob oqish'])) {
            $this->handleBooksCommand($chatId);
            return;
        }

        // 8. Reyting / Leaderboard
        if (in_array($firstWord, ['/leaderboard', '/reyting'])
            || in_array($clean, ['reyting', 'leaderboard', 'top kitobxonlar', 'eng yaxshi kitobxonlar'])) {
            $this->handleLeaderboardCommand($chatId);
            return;
        }

        // 9. Do'kon
        if (in_array($firstWord, ['/shop', '/dokon'])
            || in_array($clean, ['dokon', 'market', 'magazin'])) {
            $this->handleShopCommand($chatId);
            return;
        }

        // 10. Yordam
        if (in_array($firstWord, ['/help', '/yordam'])
            || in_array($clean, ['yordam', 'help', 'qollanma', 'yordam kerak'])) {
            $this->handleHelpCommand($chatId);
            return;
        }

        // 10.1. Bildirishnoma sozlamalari
        if (in_array($firstWord, ['/settings', '/sozlamalar', '/bildirishnomalar'])
            || in_array($clean, ['bildirishnomalar', 'sozlamalar', 'settings', 'bildirishnoma sozlamalari'])) {
            $this->handleSettingsCommand($chatId, $userId);
            return;
        }

        // 11. Mini App
        if (in_array($firstWord, ['/app', '/miniapp'])
            || in_array($clean, ['app', 'miniapp', 'mini app'])) {
            $this->handleAppCommand($chatId);
            return;
        }

        // 12. Stop
        if ($firstWord === '/stop' || $clean === 'stop') {
            $this->handleStopCommand($chatId, $userId);
            return;
        }

        // 13. Bekor qilish (Menyuni yopish)
        if (in_array($clean, ['bekor qilish', 'bekor', 'cancel'])) {
            Cache::forget("tg_reg_token_{$userId}");
            Cache::forget("tg_reg_prompt_just_sent_{$userId}");
            Cache::forget("tg_user_verifying_{$userId}");
            Cache::forget("tg_verify_prompt_just_sent_{$userId}");
            $this->handleCloseMenuCommand($chatId);
            return;
        }

        $this->handleDefaultMessage($chatId, $text);
    }

    /**
     * /start buyrug'i.
     */
    protected function handleStartCommand(int|string $chatId, array $from, string $payload = ''): void
    {
        $name = htmlspecialchars($from['first_name'] ?? 'Kitobxon');
        $userId = $from['id'] ?? 0;

        // Foydalanuvchining botdagi bloklanganlik holatini bekor qilamiz
        User::where('telegram_id', $userId)
            ->whereNotNull('telegram_blocked_at')
            ->update(['telegram_blocked_at' => null]);

        // 1. Agar bu ro'yxatdan o'tishda telefon tasdiqlash deep-linki bo'lsa: /start reg_{token}
        if (str_starts_with($payload, 'reg_')) {
            $token = substr($payload, 4);
            $pending = Cache::get("pending_reg_{$token}");

            if ($pending) {
                // Agar aynan shu kiritilgan telefon raqami allaqachon tasdiqlangan bo'lsa
                $existingUser = User::where('phone', $pending['phone'])
                    ->whereNotNull('phone_verified_at')
                    ->first();

                if ($existingUser) {
                    $this->client->sendMessage($chatId, "Assalomu alaykum, <b>{$name}</b>! 👋📚\n\nSizning telefon raqamingiz allaqachon muvaffaqiyatli tasdiqlangan va hisobingiz faol! ✅\n\nPastdagi menyu yoki to'rtburchak (⊞) tugmasi orqali kerakli bo'limni tanlashingiz mumkin 👇", [
                        'reply_markup' => $this->getMainMenuKeyboard(),
                    ]);
                    return;
                }

                Cache::put("tg_reg_token_{$userId}", $token, now()->addMinutes(15));
                Cache::put("tg_reg_prompt_just_sent_{$userId}", true, now()->addSeconds(15));
                $masked = PhoneNumber::mask($pending['phone']);

                $this->client->sendMessage($chatId, "Assalomu alaykum, <b>{$pending['name']}</b>! 👋📚\n\nPlatformada ro'yxatdan o'tish uchun <b>{$masked}</b> telefon raqamingizni tasdiqlashingiz lozim.\n\nIltimos, pastdagi <b>«📲 Raqamni ulashish (Tasdiqlash)»</b> tugmasini bosing 👇", [
                    'reply_markup' => $this->getContactRequestKeyboard(),
                ]);
                return;
            } else {
                $this->client->sendMessage($chatId, "⚠️ Ro'yxatdan o'tish havolasi muddati eskirgan (15 daqiqadan oshgan) yoki topilmadi.\n\nIltimos, saytga qaytib, ro'yxatdan o'tish formasini qaytadan yuboring.", [
                    'reply_markup' => $this->getMainMenuKeyboard(),
                ]);
                return;
            }
        }

        // 2. Agar bu kirishda 6 xonali kod olish deep-linki bo'lsa: /start login_code_{token}
        if (str_starts_with($payload, 'login_code_')) {
            $token = substr($payload, 11);
            $loginData = Cache::get("login_2fa_{$token}");

            if ($loginData) {
                $user = User::find($loginData['user_id']);
                if ($user) {
                    // Agar bu telegram_id boshqa foydalanuvchida qolgan bo'lsa, avval uni bo'shatamiz
                    User::where('telegram_id', $userId)
                        ->where('id', '!=', $user->id)
                        ->update(['telegram_id' => null, 'telegram_username' => null]);

                    $user->update([
                        'telegram_id'        => $userId,
                        'telegram_username'  => $from['username'] ?? null,
                        'telegram_linked_at' => now(),
                    ]);

                    // Eski ro'yxatdan o'tish yoki raqam ulashish keshlarini tozalaymiz
                    Cache::forget("tg_reg_token_{$userId}");
                    Cache::forget("tg_user_verifying_{$userId}");
                    Cache::forget("tg_reg_prompt_just_sent_{$userId}");
                    Cache::forget("tg_verify_prompt_just_sent_{$userId}");

                    $this->client->sendMessage(
                        $chatId,
                        "Salom, <b>{$name}</b>! 🔐\n\nPlatformaga kirish uchun 6 xonali tasdiqlash kodingiz:\n👉 <code>{$loginData['code']}</code> 👈\n\nUshbu kodni saytga kiriting. Amal qilish muddati: 5 daqiqa.",
                        [
                            'reply_markup' => [
                                'remove_keyboard' => true,
                            ],
                        ]
                    );
                    return;
                }
            }
        }

        // 3. Agar bu telefon tasdiqlash uchun deep-link bo'lsa: /start verify_{token}
        if (str_starts_with($payload, 'verify_')) {
            $token = substr($payload, 7);
            $appUserId = Cache::get("tg_verify_token_{$token}");

            if ($appUserId) {
                Cache::put("tg_user_verifying_{$userId}", $appUserId, now()->addMinutes(15));
                Cache::put("tg_verify_prompt_just_sent_{$userId}", true, now()->addSeconds(12));

                $this->client->sendMessage($chatId, "Assalomu alaykum, <b>{$name}</b>! 👋\n\nPlatformadagi hisobingiz uchun telefon raqamingizni tasdiqlashga tayyormiz.\n\nIltimos, pastdagi <b>«📲 Raqamni ulashish (Tasdiqlash)»</b> tugmasini bosing:", [
                    'reply_markup' => $this->getContactRequestKeyboard(),
                ]);
                return;
            } else {
                $this->client->sendMessage($chatId, "⚠️ Tasdiqlash havolasi muddati eskirgan yoki topilmadi.\n\nIltimos, saytdan qaytadan urinib ko'ring.", [
                    'reply_markup' => $this->getMainMenuKeyboard(),
                ]);
                return;
            }
        }

        // 2. Agar bu parolsiz kirish (auth) deep-linki bo'lsa: /start auth_{token}
        // Xavfsizlik: Darhol tasdiqlamasdan, qurilma va kodni ko'rsatib "Tasdiqlaysizmi?" so'rovini yuboramiz!
        if (str_starts_with($payload, 'auth_')) {
            $token = substr($payload, 5);
            $authData = Cache::get("tg_auth_login_{$token}");

            if (is_array($authData)) {
                $authData['pending_from'] = [
                    'id'         => $userId,
                    'first_name' => $from['first_name'] ?? '',
                    'last_name'  => $from['last_name'] ?? '',
                    'username'   => $from['username'] ?? null,
                ];
                Cache::put("tg_auth_login_{$token}", $authData, now()->addMinutes(15));

                $device = htmlspecialchars((string) ($authData['device'] ?? 'Brauzer'));
                $ip = htmlspecialchars((string) ($authData['ip'] ?? 'Noma\'lum'));
                $code = htmlspecialchars((string) ($authData['code'] ?? ''));

                $codeLine = $code !== '' ? "• Tasdiqlash kodi: <b>{$code}</b>\n" : '';
                $confirmLabel = $code !== '' ? "✅ Ha, tasdiqlayman ({$code})" : "✅ Ha, tasdiqlayman";

                $promptText = "🔐 <b>Saytga kirishni tasdiqlaysizmi?</b>\n\n"
                    . "Salom, <b>{$name}</b>! Sizning Telegram hisobingiz orqali platformaga kirish so'rovi kelib tushdi:\n\n"
                    . "• Qurilma: <b>{$device}</b>\n"
                    . "• IP manzil: <code>{$ip}</code>\n"
                    . $codeLine . "\n"
                    . "⚠️ <i>Agar ushbu so'rovni siz boshlamagan bo'lsangiz yoki saytdagi kod mos kelmasa, darhol «❌ Yo'q, bekor qilish» tugmasini bosing!</i>";

                $this->client->sendMessage($chatId, $promptText, [
                    'reply_markup' => [
                        'inline_keyboard' => [
                            [
                                ['text' => $confirmLabel, 'callback_data' => "auth_confirm:{$token}"],
                            ],
                            [
                                ['text' => "❌ Yo'q, bekor qilish", 'callback_data' => "auth_reject:{$token}"],
                            ],
                        ],
                    ],
                ]);
                return;
            }
        }

        // Agar foydalanuvchi hozirgina (oxirgi 12 soniyada) /start reg_... yoki /start verify_... orqali kirgan bo'lsa
        // va Telegram ketidan ikkinchi oddiy /start yuborgan bo'lsa — Raqamni ulashish tugmasini ustidan bosib yubormaymiz!
        if ($payload === '') {
            // Agar foydalanuvchi allaqachon ro'yxatdan o'tgan va telefon raqami tasdiqlangan bo'lsa
            $existingVerifiedUser = User::where('telegram_id', $userId)
                ->whereNotNull('phone_verified_at')
                ->first();

            if ($existingVerifiedUser) {
                Cache::forget("tg_reg_token_{$userId}");
                Cache::forget("tg_user_verifying_{$userId}");
                Cache::forget("tg_reg_prompt_just_sent_{$userId}");
                Cache::forget("tg_verify_prompt_just_sent_{$userId}");

                $this->client->sendMessage($chatId, "Assalomu alaykum, <b>{$name}</b>! 👋📚\n\nSizning telefon raqamingiz allaqachon tasdiqlangan va hisobingiz faol! ✅\n\nPastdagi menyu orqali kerakli bo'limni tanlashingiz mumkin 👇", [
                    'reply_markup' => $this->getMainMenuKeyboard(),
                ]);
                return;
            }

            if (Cache::has("tg_reg_prompt_just_sent_{$userId}") || Cache::has("tg_verify_prompt_just_sent_{$userId}")) {
                return;
            }

            $activeRegToken = Cache::get("tg_reg_token_{$userId}");
            if ($activeRegToken && ($pending = Cache::get("pending_reg_{$activeRegToken}"))) {
                $masked = PhoneNumber::mask($pending['phone']);
                $this->client->sendMessage($chatId, "Assalomu alaykum, <b>{$pending['name']}</b>! 👋📚\n\nPlatformada ro'yxatdan o'tishni yakunlash uchun <b>{$masked}</b> telefon raqamingizni tasdiqlang.\n\nPastdagi <b>«📲 Raqamni ulashish (Tasdiqlash)»</b> tugmasini bosing 👇", [
                    'reply_markup' => $this->getContactRequestKeyboard(),
                ]);
                return;
            }

            if (Cache::has("tg_user_verifying_{$userId}")) {
                $this->client->sendMessage($chatId, "Assalomu alaykum, <b>{$name}</b>! 👋\n\nTelefon raqamingizni tasdiqlash uchun pastdagi <b>«📲 Raqamni ulashish (Tasdiqlash)»</b> tugmasini bosing 👇", [
                    'reply_markup' => $this->getContactRequestKeyboard(),
                ]);
                return;
            }
        }

        // 3. Standart salomlashuv
        $appUrl = config('app.url', 'https://kitobxon.elitehost.uz');
        $miniAppUrl = config('telegram.mini_app_url', "{$appUrl}/tg-app");

        $text = "Assalomu alaykum, <b>{$name}</b>! 👋📚\n\n"
              . "<b>«Kitobxon»</b> platformasining rasmiy botiga xush kelibsiz!\n\n"
              . "Bu yerda siz:\n"
              . "• Har kuni kitob o'qib, o'z <b>Streak</b> 🔥 ko'rsatkichingizni oshirib borasiz;\n"
              . "• Testlarni topshirib, <b>oltin tangalar</b> 🪙 jamg'arasiz;\n"
              . "• Sara kitoblar va qizg'in reytingda doimiy qatnashasiz.\n\n"
              . "🌐 <b>Veb-sayt:</b> {$appUrl}\n\n"
              . "Pastdagi to'rtburchak (⊞) menyu tugmalari orqali kerakli bo'limni tanlang 👇";

        $this->client->sendMessage($chatId, $text, [
            'reply_markup' => $this->getMainMenuKeyboard(),
        ]);
    }

    /**
     * /app buyrug'i (Mini App ochish).
     */
    protected function handleAppCommand(int|string $chatId): void
    {
        $miniAppUrl = config('telegram.mini_app_url', config('app.url') . '/tg-app');
        $text = "📱 <b>«Kitobxon» Mini App</b>\n\nTelegram ichida kitoblar mutolaa qiling, testlar yeching va tangalarni boshqaring! 📖✨";

        $buttons = [
            'inline_keyboard' => [
                [
                    ['text' => '🚀 Mini Appni ochish', 'web_app' => ['url' => $miniAppUrl]],
                ]
            ]
        ];

        $this->client->sendMessage($chatId, $text, [
            'reply_markup' => $buttons,
        ]);
    }

    /**
     * Telefon raqamni ulashish klaviaturasi (ReplyKeyboardMarkup).
     */
    public function getContactRequestKeyboard(): array
    {
        return [
            'keyboard' => [
                [['text' => '📲 Raqamni ulashish (Tasdiqlash)', 'request_contact' => true]],
                [['text' => '❌ Bekor qilish']],
            ],
            'resize_keyboard'   => true,
            'one_time_keyboard' => false,
        ];
    }

    /**
     * Foydalanuvchi telefon raqamini yuborganda.
     */
    protected function handleContact(int|string $chatId, int|string $userId, array $contact, array $from = []): void
    {
        $contactUserId = $contact['user_id'] ?? null;
        $rawPhone = $contact['phone_number'] ?? null;

        // Xavfsizlik: faqat o'zining shaxsiy kontaktini yuborgan bo'lishi shart
        if ((int) $contactUserId !== (int) $userId) {
            $this->client->sendMessage($chatId, "⚠️ Iltimos, faqat o'zingizning telefon raqamingizni ulashing.");
            return;
        }

        $normalized = PhoneNumber::normalize($rawPhone);
        if (!$normalized) {
            $this->client->sendMessage($chatId, "⚠️ Telefon raqam formati noto'g'ri.", [
                'reply_markup' => $this->getMainMenuKeyboard(),
            ]);
            return;
        }

        // 1. Yangi ro'yxatdan o'tish (Registration verification)
        $regToken = Cache::get("tg_reg_token_{$userId}") ?: Cache::get("pending_reg_token_by_phone_{$normalized}");
        if ($regToken) {
            $pending = Cache::get("pending_reg_{$regToken}");
            if ($pending) {
                if ($normalized !== $pending['phone']) {
                    $this->client->sendMessage($chatId, "⚠️ Siz saytda kiritgan raqam (<b>" . PhoneNumber::mask($pending['phone']) . "</b>) bilan Telegram hisobingizdagi raqam (<b>" . PhoneNumber::mask($normalized) . "</b>) mos kelmadi.\n\nIltimos, o'zingizning haqiqiy raqamingiz bilan ro'yxatdan o'ting.");
                    return;
                }

                $welcomeCoins = (int) setting('welcome_bonus_coins', 50);

                // Agar bu telegram_id boshqa foydalanuvchida qolgan bo'lsa, avval uni bo'shatamiz
                User::where('telegram_id', $userId)
                    ->update(['telegram_id' => null, 'telegram_username' => null]);

                // Agar shu telefon raqami bilan avval tasdiqlanmagan yozuv bo'lsa, uni yangilaymiz yoki yangi ochamiz
                $existingByPhone = User::where('phone', $pending['phone'])->first();
                if ($existingByPhone) {
                    $existingByPhone->update([
                        'name'              => $pending['name'],
                        'username'          => $pending['username'],
                        'email'             => $pending['email'] ?? $existingByPhone->email,
                        'password'          => $pending['password'],
                        'phone_verified_at' => now(),
                        'telegram_id'       => $userId,
                        'telegram_username' => $from['username'] ?? $existingByPhone->telegram_username,
                        'telegram_linked_at'=> now(),
                    ]);
                    $user = $existingByPhone;
                } else {
                    $user = User::create([
                        'name'              => $pending['name'],
                        'username'          => $pending['username'],
                        'email'             => $pending['email'] ?? null,
                        'phone'             => $pending['phone'],
                        'password'          => $pending['password'],
                        'phone_verified_at' => now(),
                        'telegram_id'       => $userId,
                        'telegram_username' => $from['username'] ?? null,
                        'telegram_linked_at'=> now(),
                        'role'              => 'reader',
                        'total_points'      => 0,
                        'coin_balance'      => $welcomeCoins,
                    ]);
                }

                if ($welcomeCoins > 0) {
                    try {
                        \App\Models\CoinTransaction::create([
                            'user_id'     => $user->id,
                            'coins'       => $welcomeCoins,
                            'source'      => 'bonus',
                            'description' => 'Ro\'yxatdan o\'tish uchun xush kelibsiz bonusi 🎁',
                        ]);
                    } catch (\Throwable $e) {}
                }

                try {
                    if (method_exists($user, 'assignRole')) {
                        $user->assignRole('reader');
                    }
                } catch (\Throwable $e) {}

                \App\Models\UserProfile::create([
                    'user_id'          => $user->id,
                    'privacy_settings' => ['show_stats' => true, 'show_activity' => true],
                ]);

                \App\Models\UserStreak::create([
                    'user_id'          => $user->id,
                    'current_streak'   => 0,
                    'longest_streak'   => 0,
                    'last_active_date' => null,
                ]);

                Cache::put("pending_reg_completed_{$regToken}", $user->id, now()->addMinutes(10));
                Cache::forget("tg_reg_token_{$userId}");
                Cache::forget("tg_reg_prompt_just_sent_{$userId}");

                $this->client->sendMessage($chatId, "Tabriklaymiz, <b>{$pending['name']}</b>! 🎉\n\nTelefon raqamingiz muvaffaqiyatli tasdiqlandi va hisobingiz faollashtirildi!\n\nSaytdagi sahifangiz avtomatik ochiladi. Xush kelibsiz! ✅\n\nPastdagi to'rtburchak (⊞) menyu tugmalari orqali barcha buyruqlardan foydalanishingiz mumkin 👇", [
                    'reply_markup' => $this->getMainMenuKeyboard(),
                ]);
                return;
            }
        }

        // 2. Mavjud hisobga telefon bog'lash (Account linking)
        $appUserId = Cache::get("tg_user_verifying_{$userId}");
        $user = $appUserId ? User::find($appUserId) : User::where('telegram_id', $userId)->first();

        if (!$user) {
            // Yangi foydalanuvchi bo'lsa
            $user = User::where('phone', $normalized)->first();
        }

        if ($user) {
            // Noyob telefon tekshiruvi: boshqa hisobda bu raqam band emasmi?
            $collisionUser = User::where('phone', $normalized)
                ->where('id', '!=', $user->id)
                ->first();

            if ($collisionUser) {
                $collisions = Cache::get('telegram_phone_collisions', []);
                array_unshift($collisions, [
                    'phone'           => PhoneNumber::mask($normalized),
                    'user_id'         => $user->id,
                    'existing_user_id'=> $collisionUser->id,
                    'telegram_id'     => $userId,
                    'occurred_at'     => now()->toDateTimeString(),
                ]);
                Cache::put('telegram_phone_collisions', array_slice($collisions, 0, 50), now()->addDays(30));

                $this->client->sendMessage($chatId, "⚠️ Ushbu telefon raqami (<b>" . PhoneNumber::mask($normalized) . "</b>) allaqachon boshqa hisobga ulangan.", [
                    'reply_markup' => $this->getMainMenuKeyboard(),
                ]);
                return;
            }

            $user->update([
                'telegram_id'        => $userId,
                'phone'              => $normalized,
                'phone_verified_at'  => now(),
                'telegram_linked_at' => now(),
            ]);

            Cache::forget("tg_user_verifying_{$userId}");
            Cache::forget("tg_verify_prompt_just_sent_{$userId}");

            $authService = new TelegramAuthService();
            $bonusCoins = $authService->awardWelcomeBonusIfEligible($user->fresh());
            $bonusText = $bonusCoins > 0 ? "\n\n🎁 Hisobingizga <b>+{$bonusCoins} 🪙 xush kelibsiz bonusi</b> qo'shildi!" : '';

            $this->client->sendMessage($chatId, "Tabriklaymiz! 🎉\n\nTelefon raqamingiz (<b>" . PhoneNumber::mask($normalized) . "</b>) muvaffaqiyatli tasdiqlandi va Telegram hisobingizga ulandi! ✅{$bonusText}\n\nPastdagi menyu tugmalari orqali barcha buyruqlardan foydalanishingiz mumkin 👇", [
                'reply_markup' => $this->getMainMenuKeyboard(),
            ]);
        } else {
            $this->client->sendMessage($chatId, "Rahmat! Telefon raqamingiz qabul qilindi: <b>" . PhoneNumber::mask($normalized) . "</b>. Saytda ro'yxatdan o'tganingizda ushbu raqam avtomatik bog'lanadi. ✅\n\nPastdagi menyu tugmalari orqali barcha buyruqlardan foydalanishingiz mumkin 👇", [
                'reply_markup' => $this->getMainMenuKeyboard(),
            ]);
        }
    }

    /**
     * /profil buyrug'i.
     */
    protected function handleProfileCommand(int|string $chatId, int|string $userId): void
    {
        $user = User::where('telegram_id', $userId)->first();

        if (!$user) {
            $this->client->sendMessage($chatId, "Sizning hisobingiz hali platformaga ulanmagan.\n\nSaytga kiring va <b>Sozlamalar → Telegram hisobi</b> bo'limidan hisobingizni ulang 🔗", [
                'reply_markup' => [
                    'inline_keyboard' => [
                        [['text' => '🌐 Saytga kirish', 'url' => config('app.url')]],
                    ]
                ]
            ]);
            return;
        }

        $streak = $user->streak?->current_streak ?? 0;
        $coins = number_format($user->coin_balance);
        $points = number_format($user->total_points);
        $phone = $user->phone ? PhoneNumber::mask($user->phone) : "Ko'rsatilmagan";

        $text = "👤 <b>Sizning profilingiz:</b>\n\n"
              . "• Ism: <b>{$user->name}</b>\n"
              . "• Username: <b>@{$user->username}</b>\n"
              . "• Telefon: <code>{$phone}</code>\n"
              . "• Tanga balansi: <b>{$coins} 🪙</b>\n"
              . "• Jamg'arilgan ballar: <b>{$points} ⭐️</b>\n"
              . "• Kunlik Streak: <b>{$streak} kun ketma-ket 🔥</b>\n\n"
              . "Har kuni kitob o'qishni davom eting va sovg'alarga ega bo'ling!";

        $this->client->sendMessage($chatId, $text);
    }

    /**
     * /streak buyrug'i.
     */
    protected function handleStreakCommand(int|string $chatId, int|string $userId): void
    {
        $user = User::where('telegram_id', $userId)->first();
        $streak = $user?->streak?->current_streak ?? 0;

        $text = "🔥 <b>Kundalik mutolaa zanjiri (Streak):</b>\n\n"
              . "Siz hozirda <b>{$streak} kun</b> ketma-ket kitob o'qib kelmoqdasiz!\n\n"
              . "<i>Streak uzilib qolmasligi uchun har kuni kamida bitta sahifa o'qing yoki test yeching.</i> 📚";

        $this->client->sendMessage($chatId, $text);
    }

    /**
     * /balance buyrug'i.
     */
    protected function handleBalanceCommand(int|string $chatId, int|string $userId): void
    {
        $user = User::where('telegram_id', $userId)->first();
        $coins = number_format($user?->coin_balance ?? 0);
        $points = number_format($user?->total_points ?? 0);

        $text = "🪙 <b>Sizning jamg'armangiz:</b>\n\n"
              . "• Tangalar: <b>{$coins} 🪙</b>\n"
              . "• Umumiy ballar: <b>{$points} ⭐️</b>\n\n"
              . "Tangalaringizni platformaning <b>Do'kon</b> bo'limida maxsus imtiyozlarga almashtirishingiz mumkin! 🛍";

        $this->client->sendMessage($chatId, $text);
    }

    /**
     * /hikmat buyrug'i.
     */
    protected function handleHikmatCommand(int|string $chatId): void
    {
        $quote = DailyQuote::with('book')->inRandomOrder()->first();

        if ($quote && !empty($quote->quote_text)) {
            $author = $quote->book?->author ?? ($quote->author ?? 'Donishmandlar o\'gitlari');
            $bookTitle = $quote->book?->title ? "\n\n📖 <i>«" . htmlspecialchars($quote->book->title) . "» kitobidan</i>" : '';
            $text = "✨ <b>Kun hikmati:</b>\n\n"
                  . "<i>«" . htmlspecialchars($quote->quote_text) . "»</i>\n\n"
                  . "— <b>" . htmlspecialchars($author) . "</b>"
                  . $bookTitle;
        } else {
            $text = "✨ <b>Kun hikmati:</b>\n\n"
                  . "<i>«Kitob — eng sokin va doimiy do'st, eng dono maslahatgo'y hamda eng sabr-toqatli ustozdir.»</i>\n\n"
                  . "— <b>Charlz V. Eliot</b>";
        }

        $this->client->sendMessage($chatId, $text);
    }

    /**
     * /shop buyrug'i.
     */
    protected function handleShopCommand(int|string $chatId): void
    {
        $shopUrl = rtrim(config('app.url'), '/') . '/shop';

        $text = "🛍 <b>Platforma Do'koni:</b>\n\n"
              . "Oltin tangalaringiz evaziga profilingizni bezatuvchi ramkalar, chat ranglari, streak muzlatish va test chiptalarini xarid qiling!";

        $this->client->sendMessage($chatId, $text, [
            'reply_markup' => [
                'inline_keyboard' => [
                    [['text' => '🛍 Do\'konga o\'tish', 'url' => $shopUrl]],
                ]
            ]
        ]);
    }

    /**
     * /menu buyrug'i (Asosiy buyruqlar menyusi).
     */
    protected function handleMenuCommand(int|string $chatId): void
    {
        $text = "📋 <b>Asosiy buyruqlar menyusi:</b>\n\n"
              . "Quyidagi menyu tugmalaridan birini tanlang yoki to'rtburchak (⊞) tugmasini bosing 👇";

        $this->client->sendMessage($chatId, $text, [
            'reply_markup' => $this->getMainMenuKeyboard(),
        ]);
    }

    /**
     * /close yoki «❌ Menyuni yopish» buyrug'i. Klaviaturani yopish (yashirish).
     */
    public function handleCloseMenuCommand(int|string $chatId): void
    {
        $this->client->sendMessage(
            $chatId,
            "Menyu yopildi. 🔒\n\nMenyuni qayta ochish uchun <b>/menu</b> buyrug'ini yuboring yoki quyidagi to'rtburchak (⊞) tugmasini bosing. 📖",
            [
                'reply_markup' => [
                    'remove_keyboard' => true,
                ],
            ]
        );
    }

    /**
     * /books buyrug'i (Sara kitoblar).
     */
    protected function handleBooksCommand(int|string $chatId): void
    {
        $books = \App\Models\Book::where('is_active', true)
            ->orderByDesc('views_count')
            ->limit(5)
            ->get();

        $catalogUrl = rtrim(config('app.url'), '/') . '/catalog';

        $text = "📚 <b>Eng sara va ommabop kitoblar:</b>\n\n";

        if ($books->isEmpty()) {
            $text .= "Kutubxonamizdagi sara kitoblarni veb-saytda mutolaa qilishingiz mumkin.\n\n";
        } else {
            foreach ($books as $index => $book) {
                $num = $index + 1;
                $title = htmlspecialchars($book->title);
                $author = htmlspecialchars($book->author ?? 'Muallif');
                $text .= "{$num}. 📖 <b>{$title}</b> — <i>{$author}</i>\n";
            }
            $text .= "\n";
        }

        $text .= "Yuzlab nodir kitoblar sizni kutmoqda:";

        $this->client->sendMessage($chatId, $text, [
            'reply_markup' => [
                'inline_keyboard' => [
                    [['text' => '📚 Barcha kitoblar katalogi', 'url' => $catalogUrl]],
                ]
            ]
        ]);
    }

    /**
     * /leaderboard buyrug'i (Kitobxonlar reytingi).
     */
    protected function handleLeaderboardCommand(int|string $chatId): void
    {
        $topUsers = User::whereNotNull('total_points')
            ->where('total_points', '>', 0)
            ->orderByDesc('total_points')
            ->limit(5)
            ->get();

        $leaderboardUrl = rtrim(config('app.url'), '/') . '/leaderboard';

        $text = "🏆 <b>Eng faol kitobxonlar reytingi (Top 5):</b>\n\n";

        $medals = ['🥇', '🥈', '🥉', '4️⃣', '5️⃣'];

        if ($topUsers->isEmpty()) {
            $text .= "Hozircha reyting shakllanmoqda. Har kuni kitob o'qib, birinchi o'rinni egallang! 🚀\n\n";
        } else {
            foreach ($topUsers as $index => $u) {
                $medal = $medals[$index] ?? ($index + 1) . '.';
                $points = number_format($u->total_points);
                $name = htmlspecialchars($u->name);
                $text .= "{$medal} <b>{$name}</b> — <b>{$points}</b> ball\n";
            }
            $text .= "\n";
        }

        $text .= "To'liq reyting jadvalini saytimizda kuzating:";

        $this->client->sendMessage($chatId, $text, [
            'reply_markup' => [
                'inline_keyboard' => [
                    [['text' => '🏆 To\'liq reytingni ko\'rish', 'url' => $leaderboardUrl]],
                ]
            ]
        ]);
    }

    /**
     * /help buyrug'i.
     */
    protected function handleHelpCommand(int|string $chatId): void
    {
        $text = "❓ <b>Mavjud buyruqlar va imkoniyatlar:</b>\n\n"
              . "• <b>/start</b> — Botni ishga tushirish\n"
              . "• <b>/menu</b> — Asosiy menyu tugmalarini ko'rsatish\n"
              . "• <b>/close</b> (yoki ❌ Menyuni yopish) — Menyuni yopish va yashirish\n"
              . "• <b>/profil</b> (yoki 👤 Profilim) — Shaxsiy hisob, balans va statistika\n"
              . "• <b>/streak</b> (yoki 🔥 Streak) — Kunlik mutolaa zanjiri\n"
              . "• <b>/balance</b> (yoki 🪙 Balans) — Oltin tangalar va ballar\n"
              . "• <b>/hikmat</b> (yoki ✨ Kun hikmati) — Ilhomlantiruvchi o'gitlar\n"
              . "• <b>/books</b> (yoki 📚 Kitoblar) — Sara kitoblar katalogi\n"
              . "• <b>/leaderboard</b> (yoki 🏆 Reyting) — Faol kitobxonlar reytingi\n"
              . "• <b>/shop</b> (yoki 🛍 Do'kon) — Maxsus imtiyozlar do'koni\n"
              . "• <b>/help</b> — Ushbu yordam qo'llanmasi\n"
              . "• <b>/stop</b> — Bildirishnomalarni to'xtatish\n\n"
              . "💡 <i>Pastdagi to'rtburchak (⊞) tugmasini bosib, barcha buyruqlarga tezkor o'tishingiz mumkin!</i>";

        $this->client->sendMessage($chatId, $text, [
            'reply_markup' => $this->getMainMenuKeyboard(),
        ]);
    }

    /**
     * /stop buyrug'i.
     */
    protected function handleStopCommand(int|string $chatId, int|string $userId): void
    {
        User::where('telegram_id', $userId)->update([
            'telegram_blocked_at' => now(),
        ]);

        $this->client->sendMessage($chatId, "Barcha bildirishnomalar to'xtatildi. Qayta faollashtirish uchun istalgan vaqtda /start buyrug'ini yuboring. 🔕");
    }

    /**
     * Noma'lum matn kelganda.
     */
    protected function handleDefaultMessage(int|string $chatId, string $text): void
    {
        $this->client->sendMessage($chatId, "Buyruq tushunarsiz bo'ldi. Mavjud buyruqlarni ko'rish uchun pastdagi to'rtburchak (⊞) menyu tugmalaridan foydalaning yoki /help buyrug'ini yuboring. 📖", [
            'reply_markup' => $this->getMainMenuKeyboard(),
        ]);
    }

    /**
     * Foydalanuvchi uchun asosiy menyu tugmalari (ReplyKeyboardMarkup).
     * Bu Telegram chatidagi pastki o'ng to'rtburchak (⊞) tugmasini bosganda ochiladigan buyruqlar menyusidir.
     */
    public function getMainMenuKeyboard(): array
    {
        $appUrl = config('app.url', 'https://kitobxon.elitehost.uz');
        $miniAppUrl = config('telegram.mini_app_url', rtrim($appUrl, '/') . '/tg-app');

        $row1 = [];
        if (!empty($miniAppUrl) && str_starts_with($miniAppUrl, 'https://')) {
            $row1[] = ['text' => '📖 Mutolaa', 'web_app' => ['url' => $miniAppUrl]];
        } else {
            $row1[] = ['text' => '📚 Kitoblar'];
        }
        $row1[] = ['text' => '👤 Profilim'];

        return [
            'keyboard' => [
                $row1,
                [
                    ['text' => '🔥 Streak holati'],
                    ['text' => '🪙 Tanga balansi'],
                ],
                [
                    ['text' => '✨ Kun hikmati'],
                    ['text' => '🛍 Do\'kon'],
                ],
                [
                    ['text' => '🏆 Reyting'],
                    ['text' => '⚙️ Bildirishnomalar'],
                ],
                [
                    ['text' => '❓ Yordam'],
                    ['text' => '❌ Menyuni yopish'],
                ],
            ],
            'resize_keyboard' => true,
        ];
    }

    /**
     * Bildirishnoma sozlamalari menyusi (/settings yoki ⚙️ Bildirishnomalar).
     */
    public function handleSettingsCommand(int|string $chatId, int|string $userId, ?int $editMessageId = null): void
    {
        $user = User::where('telegram_id', $userId)->first();

        if (!$user) {
            $this->client->sendMessage($chatId, "Sizning hisobingiz hali platformaga ulanmagan.\n\nSaytga kiring va <b>Sozlamalar → Telegram hisobi</b> bo'limidan hisobingizni ulang 🔗", [
                'reply_markup' => [
                    'inline_keyboard' => [
                        [['text' => '🌐 Saytga kirish', 'url' => config('app.url')]],
                    ],
                ],
            ]);
            return;
        }

        $text = "⚙️ <b>Telegram bildirishnomalarini boshqarish:</b>\n\n"
            . "Qaysi xabarlarni Telegram orqali olishni xohlaysiz? O'zingizga kerakli turlarni tanlab yoqing (✅) yoki o'chirib qo'ying (❌):\n\n"
            . "• <b>🔴 Jonli efir (Strim)</b> — Siz obuna bo'lgan kitobxon strim boshlaganda\n"
            . "• <b>🛡️ Ban / Hisob holati</b> — Hisobingiz bloklanganda yoki ban yechilganda\n"
            . "• <b>👥 Yangi obunachilar</b> — Kimdir sizga obuna bo'lganda\n"
            . "• <b>🔥 Streak eslatmasi</b> — Kechki mutolaa eslatmasi\n"
            . "• <b>✨ Kun hikmati</b> — Har kungi sara iqtiboslar\n"
            . "• <b>🪙 Tangalar va yutuqlar</b> — Mukofotlar haqida xabar\n"
            . "• <b>🌙 Tungi jimjitlik</b> — 23:00 dan 07:00 gacha bezovta qilmaslik";

        $markup = $this->getSettingsInlineKeyboard($user);

        if ($editMessageId) {
            try {
                $this->client->editMessageText($chatId, $editMessageId, $text, [
                    'reply_markup' => $markup,
                ]);
                return;
            } catch (\Throwable $e) {
                // Xabar o'zgarmagan bo'lsa yoki tahrirlab bo'lmasa
                return;
            }
        }

        $this->client->sendMessage($chatId, $text, [
            'reply_markup' => $markup,
        ]);
    }

    /**
     * Foydalanuvchining bildirishnoma sozlamalari uchun interaktiv inline tugmalar.
     */
    public function getSettingsInlineKeyboard(User $user): array
    {
        $icon = fn (string $key): string => $user->isTelegramNotificationEnabled($key) ? '✅' : '❌';

        return [
            'inline_keyboard' => [
                [
                    ['text' => $icon('live_stream') . ' 🔴 Obuna strimlari (Jonli efir)', 'callback_data' => 'toggle_pref:live_stream'],
                ],
                [
                    ['text' => $icon('ban_status') . ' 🛡️ Ban va Bandan chiqish xabari', 'callback_data' => 'toggle_pref:ban_status'],
                ],
                [
                    ['text' => $icon('new_follower') . ' 👥 Yangi obunachilar', 'callback_data' => 'toggle_pref:new_follower'],
                ],
                [
                    ['text' => $icon('streak_reminder') . ' 🔥 Streak eslatmasi', 'callback_data' => 'toggle_pref:streak_reminder'],
                    ['text' => $icon('daily_quote') . ' ✨ Kun hikmati', 'callback_data' => 'toggle_pref:daily_quote'],
                ],
                [
                    ['text' => $icon('coin_reward') . ' 🪙 Tangalar va yutuqlar', 'callback_data' => 'toggle_pref:coin_reward'],
                ],
                [
                    ['text' => $icon('quiet_hours') . ' 🌙 Tungi jimjitlik (23:00–07:00)', 'callback_data' => 'toggle_pref:quiet_hours'],
                ],
                [
                    ['text' => '🔔 Barchasini yoqish', 'callback_data' => 'toggle_pref:all_on'],
                    ['text' => '🔕 Barchasini o\'chirish', 'callback_data' => 'toggle_pref:all_off'],
                ],
            ],
        ];
    }

    /**
     * Inline tugmalar bosilganda (Callback Query).
     */
    protected function handleCallbackQuery(array $callbackQuery): void
    {
        $id = $callbackQuery['id'] ?? '';
        $data = $callbackQuery['data'] ?? '';
        $message = $callbackQuery['message'] ?? [];
        $chatId = $message['chat']['id'] ?? null;
        $messageId = isset($message['message_id']) ? (int) $message['message_id'] : null;
        $userId = $callbackQuery['from']['id'] ?? null;

        if (!$chatId || !$userId) {
            if ($id) {
                $this->client->answerCallbackQuery($id);
            }
            return;
        }

        // Bildirishnoma sozlamalarini yoqish / o'chirish (toggle_pref:...)
        if (str_starts_with($data, 'toggle_pref:')) {
            $prefKey = substr($data, 12);
            $user = User::where('telegram_id', $userId)->first();

            if (!$user) {
                if ($id) {
                    $this->client->answerCallbackQuery($id, ['text' => 'Hisobingiz ulanmagan!']);
                }
                return;
            }

            $enabled = $user->toggleTelegramPreference($prefKey);

            if ($id) {
                $toastText = match ($prefKey) {
                    'all_on'  => '🔔 Barcha bildirishnomalar yoqildi!',
                    'all_off' => '🔕 Barcha bildirishnomalar o\'chirildi!',
                    default   => $enabled ? '✅ Bildirishnoma yoqildi!' : '🔕 Bildirishnoma o\'chirildi!',
                };
                $this->client->answerCallbackQuery($id, ['text' => $toastText]);
            }

            $this->handleSettingsCommand($chatId, $userId, $messageId);
            return;
        }

        if ($id) {
            $this->client->answerCallbackQuery($id);
        }

        // Kirishni tasdiqlash yoki rad etish callback tugmalari
        if (str_starts_with($data, 'auth_confirm:')) {
            $token = substr($data, 13);
            $this->handleAuthConfirmCallback($chatId, $userId, $callbackQuery['from'] ?? [], $token);
            return;
        }

        if (str_starts_with($data, 'auth_reject:')) {
            $token = substr($data, 12);
            $this->handleAuthRejectCallback($chatId, $userId, $token);
            return;
        }

        match ($data) {
            'cmd_profil' => $this->handleProfileCommand($chatId, $userId),
            'cmd_streak' => $this->handleStreakCommand($chatId, $userId),
            'cmd_balance' => $this->handleBalanceCommand($chatId, $userId),
            'cmd_hikmat' => $this->handleHikmatCommand($chatId),
            'cmd_books' => $this->handleBooksCommand($chatId),
            'cmd_leaderboard' => $this->handleLeaderboardCommand($chatId),
            'cmd_shop' => $this->handleShopCommand($chatId),
            'cmd_help' => $this->handleHelpCommand($chatId),
            'cmd_settings' => $this->handleSettingsCommand($chatId, $userId),
            'cmd_menu' => $this->handleMenuCommand($chatId),
            'cmd_close' => $this->handleCloseMenuCommand($chatId),
            default => null,
        };
    }

    /**
     * Foydalanuvchi "Ha, tasdiqlayman" inline tugmasini bosganda kirishni yakunlash.
     */
    protected function handleAuthConfirmCallback(int|string $chatId, int|string $userId, array $from, string $token): void
    {
        $authData = Cache::get("tg_auth_login_{$token}");
        if (!is_array($authData)) {
            $this->client->sendMessage($chatId, "⚠️ Kirish so'rovi muddati tugagan yoki allaqachon yakunlangan.");
            return;
        }

        $pendingFrom = $authData['pending_from'] ?? null;
        if (is_array($pendingFrom) && isset($pendingFrom['id']) && (int) $pendingFrom['id'] !== (int) $userId) {
            $this->client->sendMessage($chatId, "⚠️ Xavfsizlik xatosi: ushbu kirish so'rovi boshqa foydalanuvchiga tegishli.");
            return;
        }

        $authService = new TelegramAuthService();
        $findResult = $authService->findOrCreateUser([
            'id'         => $userId,
            'first_name' => $from['first_name'] ?? ($pendingFrom['first_name'] ?? ''),
            'last_name'  => $from['last_name'] ?? ($pendingFrom['last_name'] ?? ''),
            'username'   => $from['username'] ?? ($pendingFrom['username'] ?? null),
        ]);

        if (!$findResult['success']) {
            $this->client->sendMessage($chatId, "⚠️ " . ($findResult['message'] ?? "Kirishda xatolik yuz berdi."));
            return;
        }

        $user = $findResult['user'];
        Cache::put("tg_auth_confirmed_{$token}", $user->id, now()->addMinutes(10));

        $completeUrl = route('auth.telegram.complete', ['token' => $token]);
        if (!empty($authData['browser_secret'])) {
            $completeUrl .= '?s=' . urlencode((string) $authData['browser_secret']);
        }

        $name = htmlspecialchars($from['first_name'] ?? $user->name);
        $phoneNote = !$user->hasVerifiedPhone()
            ? "\n\n🎁 <b>50 tanga xush kelibsiz bonusini</b> olish uchun telefon raqamingizni tasdiqlashni unutmang!"
            : '';

        $this->client->sendMessage($chatId, "Salom, <b>{$name}</b>! ✅\n\nSaytga kirishingiz muvaffaqiyatli tasdiqlandi!{$phoneNote}\n\nBrauzer sahifangiz avtomatik yangilanadi. Agar yangilanmasa, quyidagi tugma orqali to'g'ridan-to'g'ri saytga kiring:", [
            'reply_markup' => [
                'inline_keyboard' => [
                    [
                        ['text' => '🚀 Saytga kirish', 'url' => $completeUrl],
                    ],
                ],
            ],
        ]);
    }

    /**
     * Foydalanuvchi "Yo'q, bekor qilish" inline tugmasini bosganda kirishni bloklash.
     */
    protected function handleAuthRejectCallback(int|string $chatId, int|string $userId, string $token): void
    {
        Cache::forget("tg_auth_login_{$token}");
        Cache::forget("tg_auth_confirmed_{$token}");
        Cache::put("tg_auth_rejected_{$token}", true, now()->addMinutes(5));

        $this->client->sendMessage($chatId, "🛑 <b>Kirish so'rovi bekor qilindi!</b>\n\nHisobingiz xavfsizlikda. Brauzerdagi kirish oynasi avtomatik yopildi.", [
            'reply_markup' => $this->getMainMenuKeyboard(),
        ]);
    }
}
