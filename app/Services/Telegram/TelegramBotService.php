<?php

namespace App\Services\Telegram;

use App\Models\DailyQuote;
use App\Models\User;
use App\Support\PhoneNumber;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class TelegramBotService
{
    protected TelegramClient $client;

    public function __construct(?TelegramClient $client = null)
    {
        $this->client = $client ?? new TelegramClient();
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
            $this->handleContact($chatId, $userId, $message['contact']);
            return;
        }

        $text = trim($message['text'] ?? '');
        if ($text === '') {
            return;
        }

        // Buyruqlarni ajratib olish (/start payload)
        $parts = explode(' ', $text, 2);
        $command = strtolower($parts[0]);
        $payload = trim($parts[1] ?? '');

        match ($command) {
            '/start' => $this->handleStartCommand($chatId, $from, $payload),
            '/profil', '/profile' => $this->handleProfileCommand($chatId, $userId),
            '/streak' => $this->handleStreakCommand($chatId, $userId),
            '/balance', '/balans' => $this->handleBalanceCommand($chatId, $userId),
            '/hikmat' => $this->handleHikmatCommand($chatId),
            '/shop', '/dokon' => $this->handleShopCommand($chatId),
            '/help', '/yordam' => $this->handleHelpCommand($chatId),
            '/app', '/miniapp' => $this->handleAppCommand($chatId),
            '/stop' => $this->handleStopCommand($chatId, $userId),
            default => $this->handleDefaultMessage($chatId, $text),
        };
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
                Cache::put("tg_reg_token_{$userId}", $token, now()->addMinutes(15));
                $masked = PhoneNumber::mask($pending['phone']);

                $this->client->sendMessage($chatId, "Assalomu alaykum, <b>{$pending['name']}</b>! 👋📚\n\nPlatformada ro'yxatdan o'tish uchun <b>{$masked}</b> telefon raqamingizni tasdiqlashingiz lozim.\n\nIltimos, pastdagi <b>«📲 Raqamni ulashish (Tasdiqlash)»</b> tugmasini bosing:", [
                    'reply_markup' => [
                        'keyboard' => [
                            [['text' => '📲 Raqamni ulashish (Tasdiqlash)', 'request_contact' => true]]
                        ],
                        'resize_keyboard' => true,
                        'one_time_keyboard' => true,
                    ]
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
                    $user->update([
                        'telegram_id'        => $userId,
                        'telegram_username'  => $from['username'] ?? null,
                        'telegram_linked_at' => now(),
                    ]);

                    $this->client->sendMessage($chatId, "Salom, <b>{$name}</b>! 🔐\n\nPlatformaga kirish uchun 6 xonali tasdiqlash kodingiz:\n👉 <code>{$loginData['code']}</code> 👈\n\nUshbu kodni saytga kiriting. Amal qilish muddati: 5 daqiqa.");
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

                $this->client->sendMessage($chatId, "Assalomu alaykum, <b>{$name}</b>! 👋\n\nPlatformadagi hisobingiz uchun telefon raqamingizni tasdiqlashga tayyormiz.\n\nIltimos, pastdagi <b>«📲 Raqamni ulashish»</b> tugmasini bosing:", [
                    'reply_markup' => [
                        'keyboard' => [
                            [['text' => '📲 Raqamni ulashish (Tasdiqlash)', 'request_contact' => true]]
                        ],
                        'resize_keyboard' => true,
                        'one_time_keyboard' => true,
                    ]
                ]);
                return;
            }
        }

        // 2. Agar bu parolsiz kirish (auth) deep-linki bo'lsa: /start auth_{token}
        if (str_starts_with($payload, 'auth_')) {
            $token = substr($payload, 5);
            $authData = Cache::get("tg_auth_login_{$token}");

            if ($authData) {
                // Foydalanuvchini topish yoki yaratish
                $authService = new TelegramAuthService();
                $findResult = $authService->findOrCreateUser([
                    'id'         => $userId,
                    'first_name' => $from['first_name'] ?? '',
                    'last_name'  => $from['last_name'] ?? '',
                    'username'   => $from['username'] ?? null,
                ]);

                if ($findResult['success']) {
                    Cache::put("tg_auth_confirmed_{$token}", $findResult['user']->id, now()->addMinutes(10));
                    $completeUrl = route('auth.telegram.complete', ['token' => $token]);

                    $this->client->sendMessage($chatId, "Salom, <b>{$name}</b>! ✅\n\nSaytga kirishingiz muvaffaqiyatli tasdiqlandi!\n\nBrauzer sahifangiz avtomatik yangilanadi. Agar yangilanmasa, quyidagi tugma orqali to'g'ridan-to'g'ri saytga kiring:", [
                        'reply_markup' => [
                            'inline_keyboard' => [
                                [
                                    ['text' => '🚀 Saytga kirish', 'url' => $completeUrl]
                                ]
                            ]
                        ]
                    ]);
                    return;
                }
            }
        }

        // 3. Standart salomlashuv
        $appUrl = config('app.url', 'https://kitob-9y1h.onrender.com');
        $miniAppUrl = config('telegram.mini_app_url', "{$appUrl}/tg-app");

        $text = "Assalomu alaykum, <b>{$name}</b>! 👋📚\n\n"
              . "<b>«Kitobxon»</b> platformasining rasmiy botiga xush kelibsiz!\n\n"
              . "Bu yerda siz:\n"
              . "• Har kuni kitob o'qib, o'z <b>Streak</b> 🔥 ko'rsatkichingizni oshirib borasiz;\n"
              . "• Testlarni topshirib, <b>oltin tangalar</b> 🪙 jamg'arasiz;\n"
              . "• Yangi kitoblar va muhim yangiliklardan doimiy xabardor bo'lasiz.\n\n"
              . "Quyidagi tugmalar orqali platformadan foydalaning 👇";

        $firstRow = [];
        if (!empty($miniAppUrl) && str_starts_with($miniAppUrl, 'https://')) {
            $firstRow[] = ['text' => '📱 Mini App (Mutolaa)', 'web_app' => ['url' => $miniAppUrl]];
        }
        $firstRow[] = ['text' => '🌐 Veb-sayt', 'url' => $appUrl];

        $buttons = [
            'inline_keyboard' => [
                $firstRow,
                [
                    ['text' => '👤 Profilim', 'callback_data' => 'cmd_profil'],
                    ['text' => '🔥 Streak holati', 'callback_data' => 'cmd_streak'],
                ],
                [
                    ['text' => '🪙 Tanga balansi', 'callback_data' => 'cmd_balance'],
                    ['text' => '✨ Kun hikmati', 'callback_data' => 'cmd_hikmat'],
                ],
            ]
        ];

        $this->client->sendMessage($chatId, $text, [
            'reply_markup' => $buttons,
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
     * Foydalanuvchi telefon raqamini yuborganda.
     */
    protected function handleContact(int|string $chatId, int|string $userId, array $contact): void
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
            $this->client->sendMessage($chatId, "⚠️ Telefon raqam formati noto'g'ri.");
            return;
        }

        // 1. Yangi ro'yxatdan o'tish (Registration verification)
        $regToken = Cache::get("tg_reg_token_{$userId}");
        if ($regToken) {
            $pending = Cache::get("pending_reg_{$regToken}");
            if ($pending) {
                if ($normalized !== $pending['phone']) {
                    $this->client->sendMessage($chatId, "⚠️ Siz saytda kiritgan raqam (<b>" . PhoneNumber::mask($pending['phone']) . "</b>) bilan Telegram hisobingizdagi raqam (<b>" . PhoneNumber::mask($normalized) . "</b>) mos kelmadi.\n\nIltimos, o'zingizning haqiqiy raqamingiz bilan ro'yxatdan o'ting.");
                    return;
                }

                $welcomeCoins = (int) setting('welcome_bonus_coins', 50);

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

                $this->client->sendMessage($chatId, "Tabriklaymiz, <b>{$pending['name']}</b>! 🎉\n\nTelefon raqamingiz muvaffaqiyatli tasdiqlandi va hisobingiz faollashtirildi!\n\nSaytdagi sahifangiz avtomatik ochiladi. Xush kelibsiz! ✅", [
                    'reply_markup' => ['remove_keyboard' => true],
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
            $user->update([
                'telegram_id'        => $userId,
                'phone'              => $normalized,
                'phone_verified_at'  => now(),
                'telegram_linked_at' => now(),
            ]);

            $this->client->sendMessage($chatId, "Tabriklaymiz! 🎉\n\nTelefon raqamingiz (<b>" . PhoneNumber::mask($normalized) . "</b>) muvaffaqiyatli tasdiqlandi va Telegram hisobingizga ulandi! ✅", [
                'reply_markup' => ['remove_keyboard' => true],
            ]);
        } else {
            $this->client->sendMessage($chatId, "Rahmat! Telefon raqamingiz qabul qilindi: <b>" . PhoneNumber::mask($normalized) . "</b>. Saytda ro'yxatdan o'tganingizda ushbu raqam avtomatik bog'lanadi. ✅", [
                'reply_markup' => ['remove_keyboard' => true],
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
     * /help buyrug'i.
     */
    protected function handleHelpCommand(int|string $chatId): void
    {
        $text = "❓ <b>Mavjud buyruqlar ro'yxati:</b>\n\n"
              . "• /start — Botni ishga tushirish\n"
              . "• /profil — Profil ma'lumotlari va balans\n"
              . "• /streak — Kunlik zanjir holati\n"
              . "• /balance — Tanga va ballar\n"
              . "• /hikmat — Ilhomlantiruvchi kun hikmati\n"
              . "• /shop — Do'kon va maxsus imtiyozlar\n"
              . "• /help — Yordam va qo'llanma\n"
              . "• /stop — Bildirishnomalarni to'xtatish";

        $this->client->sendMessage($chatId, $text);
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
        $this->client->sendMessage($chatId, "Buyruq tushunarsiz bo'ldi. Mavjud imkoniyatlarni ko'rish uchun /help buyrug'ini yuboring. 📖");
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
        $userId = $callbackQuery['from']['id'] ?? null;

        if ($id) {
            $this->client->answerCallbackQuery($id);
        }

        if (!$chatId || !$userId) {
            return;
        }

        match ($data) {
            'cmd_profil' => $this->handleProfileCommand($chatId, $userId),
            'cmd_streak' => $this->handleStreakCommand($chatId, $userId),
            'cmd_balance' => $this->handleBalanceCommand($chatId, $userId),
            'cmd_hikmat' => $this->handleHikmatCommand($chatId),
            default => null,
        };
    }
}
