<?php

namespace Database\Seeders;

use App\Models\ShopItem;
use App\Services\Gamification\ProfileVisualService;
use Illuminate\Database\Seeder;

class ShopItemsSeeder extends Seeder
{
    /**
     * Agar true bo'lsa, mavjud buyumlarning narxi va parametrlarini yangilaydi (updateOrCreate).
     * False bo'lsa (standart), adminning o'zgartirishlarini saqlab qoladi (firstOrCreate).
     */
    public static bool $refreshDefaults = false;

    /**
     * Buyumni xavfsiz yaratish yoki yangilash (standart: firstOrCreate, --refresh-defaults: updateOrCreate).
     */
    protected function seedItem(array $attributes, array $values): ShopItem
    {
        $shouldRefresh = static::$refreshDefaults
            || (isset($_SERVER['argv']) && in_array('--refresh-defaults', $_SERVER['argv'], true));

        if ($shouldRefresh) {
            return ShopItem::updateOrCreate($attributes, $values);
        }

        return ShopItem::firstOrCreate($attributes, $values);
    }

    /**
     * Do'kon standart katalogi: 7 ta mavzu va Streak Freeze.
     */
    public function run(): void
    {
        // 1. 7 ta profil mavzusi (bundle)
        $themes = ProfileVisualService::THEMES;
        $order = 10;

        $themeDescriptionsRu = [
            'aurora' => 'Бирюзовое и изумрудное сияние. Магическое северное сияние и аура неона в вашем профиле.',
            'cyber' => 'Ало-розовый киберпанк. Футуристическая неоновая сетка и атмосфера хакера.',
            'gold' => 'Аристократические золотые лучи. Роскошное королевское сияние и звезды.',
            'emerald' => 'Драгоценный изумрудный кристалл. Очарование природы и элегантная изумрудная зелень.',
            'obsidian' => 'Таинственный галактический космос. Бесконечная вселенная и фиолетовая туманность.',
            'library' => 'Винтажные кожаные переплеты, теплый янтарь и спокойная атмосфера чтения.',
            'sunset' => 'Огненно-фиолетовый закат. Спокойная романтическая гамма вечернего чтения.',
        ];

        $themeDescriptionsEn = [
            'aurora' => 'Teal and emerald shimmer. Magical northern lights and vivid neon aura for your profile.',
            'cyber' => 'Vibrant cyberpunk glow. Futuristic neon grid and cybernetic aura.',
            'gold' => 'Royal gold aura. Aristocratic brilliance, star crowns, and luxury background.',
            'emerald' => 'Precious emerald crystal. Natural serenity and elegant green radiance.',
            'obsidian' => 'Mysterious cosmic void. Endless universe, deep purple nebula, and distant galaxies.',
            'library' => 'Antique leather covers, warm amber wood, and serene vintage library atmosphere.',
            'sunset' => 'Fiery purple sunset radiance. Romantic solitude for evening reading moments.',
        ];

        $themeNamesRu = [
            'aurora' => 'Северное сияние (Aurora Neon)',
            'cyber' => 'Кибер Неон (Cyberpunk)',
            'gold' => 'Королевское Золото (Royal Gold)',
            'emerald' => 'Изумрудный Луч (Emerald Realm)',
            'obsidian' => 'Космическая Бездна (Obsidian Void)',
            'library' => 'Винтажная Библиотека (Vintage Library)',
            'sunset' => 'Закатное Сияние (Sunset Solitude)',
        ];

        $themeNamesEn = [
            'aurora' => 'Northern Lights (Aurora Neon)',
            'cyber' => 'Cyber Neon (Cyberpunk)',
            'gold' => 'Royal Gold',
            'emerald' => 'Emerald Realm',
            'obsidian' => 'Obsidian Void',
            'library' => 'Vintage Library',
            'sunset' => 'Sunset Solitude',
        ];

        foreach ($themes as $key => $t) {
            $order += 10;
            $rarity = match (true) {
                $t['price'] >= 150 => 'legendary',
                $t['price'] >= 110 => 'epic',
                $t['price'] >= 90  => 'rare',
                default            => 'common',
            };

            $payload = array_merge($t, [
                'bundle_slots' => ['profile_theme', 'avatar_frame', 'chat_bubble'],
            ]);

            $this->seedItem(
                ['key' => $key],
                [
                    'category'         => 'bundle',
                    'slot'             => 'profile_theme',
                    'name'             => [
                        'uz' => $t['name'],
                        'ru' => $themeNamesRu[$key] ?? $t['name'],
                        'en' => $themeNamesEn[$key] ?? $t['name'],
                    ],
                    'description'      => [
                        'uz' => $t['description'],
                        'ru' => $themeDescriptionsRu[$key] ?? $t['description'],
                        'en' => $themeDescriptionsEn[$key] ?? $t['description'],
                    ],
                    'price_coins'      => (int) $t['price'],
                    'rarity'           => $rarity,
                    'payload'          => $payload,
                    'is_consumable'    => false,
                    'max_quantity'     => 1,
                    'duration_minutes' => null,
                    'is_active'        => true,
                    'sort_order'       => $order,
                ]
            );
        }

        // 2. Streak Freeze (Muzlatgich)
        $this->seedItem(
            ['key' => 'streak_freeze'],
            [
                'category'         => 'consumable',
                'slot'             => null,
                'name'             => [
                    'uz' => 'Streak Muzlatgich',
                    'ru' => 'Заморозка серии',
                    'en' => 'Streak Freeze',
                ],
                'description'      => [
                    'uz' => 'O‘qish uzluksizligini (streak) 1 kunga muzlatadi va ketma-ketlik buzilishining oldini oladi.',
                    'ru' => 'Замораживает серию непрерывного чтения на 1 день и спасает ваш прогресс.',
                    'en' => 'Freezes your reading streak for 1 day, protecting your hard-earned progress.',
                ],
                'price_coins'      => 50,
                'rarity'           => 'common',
                'payload'          => [
                    'effect'    => 'streak_freeze',
                    'icon'      => 'snowflake',
                    'max_limit' => 5,
                ],
                'is_consumable'    => true,
                'max_quantity'     => 5,
                'duration_minutes' => null,
                'is_active'        => true,
                'sort_order'       => 1,
            ]
        );

        // 3. Tanga kuchaytirgich (Coin Booster 60 min)
        $this->seedItem(
            ['key' => 'coin_booster_60'],
            [
                'category'         => 'consumable',
                'slot'             => null,
                'name'             => [
                    'uz' => 'Tanga kuchaytirgich (1 soat)',
                    'ru' => 'Усилитель монет (1 час)',
                    'en' => 'Coin Booster (1 hour)',
                ],
                'description'      => [
                    'uz' => '60 daqiqa davomida kitob mutolaasidan 2 barobar ko‘proq tanga yig‘ing (faqat tanga, ball o‘zgarmaydi).',
                    'ru' => 'Получайте в 2 раза больше монет за чтение в течение 60 минут (только монеты, без влияния на рейтинг).',
                    'en' => 'Earn 2x coins while reading for 60 minutes (coins only, does not affect leaderboard points).',
                ],
                'price_coins'      => 35,
                'rarity'           => 'rare',
                'payload'          => [
                    'effect'     => 'coin_booster',
                    'multiplier' => 2.0,
                    'duration'   => 60,
                ],
                'is_consumable'    => true,
                'max_quantity'     => 3,
                'duration_minutes' => 60,
                'is_active'        => true,
                'sort_order'       => 2,
            ]
        );

        // 4. Viktorinani qayta urinish chiptasi
        $this->seedItem(
            ['key' => 'quiz_retry_ticket'],
            [
                'category'         => 'consumable',
                'slot'             => null,
                'name'             => [
                    'uz' => 'Viktorina qayta urinish chiptasi',
                    'ru' => 'Билет пересдачи викторины',
                    'en' => 'Quiz Retry Ticket',
                ],
                'description'      => [
                    'uz' => 'Muvaffaqiyatsiz yakunlangan kitob viktorinasini zudlik bilan qayta topshirish imkoniyati.',
                    'ru' => 'Позволяет моментально пересдать неудачную викторину по книге.',
                    'en' => 'Instantly retry a failed book quiz without waiting.',
                ],
                'price_coins'      => 35,
                'rarity'           => 'common',
                'payload'          => [
                    'effect' => 'quiz_retry',
                ],
                'is_consumable'    => true,
                'max_quantity'     => 10,
                'duration_minutes' => null,
                'is_active'        => true,
                'sort_order'       => 3,
            ]
        );

        // 5. Unvonlar (Titles)
        $titles = [
            [
                'key'         => 'title_night_reader',
                'name_uz'     => 'Tungi o‘quvchi',
                'name_ru'     => 'Ночной читатель',
                'name_en'     => 'Night Reader',
                'desc_uz'     => 'Yarim kechada mutolaa qiluvchi sadoqatli kitobxon unvoni.',
                'desc_ru'     => 'Титул для тех, кто читает под светом ночных звезд.',
                'desc_en'     => 'Title for those who read under the starlight.',
                'price'       => 80,
                'rarity'      => 'rare',
                'badge_color' => '#818CF8',
            ],
            [
                'key'         => 'title_bookworm',
                'name_uz'     => 'Kitob g‘avvosi',
                'name_ru'     => 'Книжный ныряльщик',
                'name_en'     => 'Book Diver',
                'desc_uz'     => 'Sahifalar tubiga sho‘ng‘uvchi asil kitobxon unvoni.',
                'desc_ru'     => 'Титул истинного ценителя глубины книг.',
                'desc_en'     => 'Title for deep readers who explore every page.',
                'price'       => 100,
                'rarity'      => 'rare',
                'badge_color' => '#34D399',
            ],
            [
                'key'         => 'title_philosopher',
                'name_uz'     => 'Fikr donishmandi',
                'name_ru'     => 'Мудрец мыслей',
                'name_en'     => 'Sage of Thoughts',
                'desc_uz'     => 'Falsafiy va teran asarlar o‘quvchisi uchun faxriy unvon.',
                'desc_ru'     => 'Почетный титул для читателей глубоких мыслей.',
                'desc_en'     => 'Honorary title for philosophical and mindful readers.',
                'price'       => 160,
                'rarity'      => 'epic',
                'badge_color' => '#A78BFA',
            ],
            [
                'key'         => 'title_scholar',
                'name_uz'     => 'Akademik o‘quvchi',
                'name_ru'     => 'Академический чтец',
                'name_en'     => 'Grand Scholar',
                'desc_uz'     => 'Platformadagi eng oliy darajadagi nufuzli kitobxon unvoni.',
                'desc_ru'     => 'Высший академический титул читателя.',
                'desc_en'     => 'The highest academic reader title on the platform.',
                'price'       => 280,
                'rarity'      => 'legendary',
                'badge_color' => '#FBBF24',
            ],
        ];

        foreach ($titles as $t) {
            $this->seedItem(
                ['key' => $t['key']],
                [
                    'category'         => 'profile',
                    'slot'             => 'title',
                    'name'             => [
                        'uz' => $t['name_uz'],
                        'ru' => $t['name_ru'],
                        'en' => $t['name_en'],
                    ],
                    'description'      => [
                        'uz' => $t['desc_uz'],
                        'ru' => $t['desc_ru'],
                        'en' => $t['desc_en'],
                    ],
                    'price_coins'      => $t['price'],
                    'rarity'           => $t['rarity'],
                    'payload'          => [
                        'badge_color' => $t['badge_color'],
                        'display_text'=> $t['name_uz'],
                    ],
                    'is_consumable'    => false,
                    'max_quantity'     => 1,
                    'duration_minutes' => null,
                    'is_active'        => true,
                    'sort_order'       => 20,
                ]
            );
        }

        // 6. Ism ranglari (Name Colors)
        $nameColors = [
            [
                'key'     => 'name_gold',
                'name_uz' => 'Oltin ism jilosi',
                'name_ru' => 'Золотое сияние имени',
                'name_en' => 'Royal Gold Name Glow',
                'color'   => '#F59E0B',
                'price'   => 120,
                'rarity'  => 'epic',
            ],
            [
                'key'     => 'name_emerald',
                'name_uz' => 'Zumrad ism jilosi',
                'name_ru' => 'Изумрудный цвет имени',
                'name_en' => 'Emerald Name Glow',
                'color'   => '#10B981',
                'price'   => 90,
                'rarity'  => 'rare',
            ],
            [
                'key'     => 'name_rose',
                'name_uz' => 'Neon atirgul ismi',
                'name_ru' => 'Неоновый розовый цвет имени',
                'name_en' => 'Neon Rose Name Glow',
                'color'   => '#F43F5E',
                'price'   => 110,
                'rarity'  => 'epic',
            ],
            [
                'key'     => 'name_violet',
                'name_uz' => 'Ametist binafsha ism',
                'name_ru' => 'Аметистовый цвет имени',
                'name_en' => 'Amethyst Name Glow',
                'color'   => '#8B5CF6',
                'price'   => 95,
                'rarity'  => 'rare',
            ],
        ];

        foreach ($nameColors as $nc) {
            $this->seedItem(
                ['key' => $nc['key']],
                [
                    'category'         => 'profile',
                    'slot'             => 'name_color',
                    'name'             => [
                        'uz' => $nc['name_uz'],
                        'ru' => $nc['name_ru'],
                        'en' => $nc['name_en'],
                    ],
                    'description'      => [
                        'uz' => 'Profilingiz, chat va reytingda ismingizning jozibador alohida rangda ko‘rinishi.',
                        'ru' => 'Особый цвет вашего имени в профиле, чате и таблице лидеров.',
                        'en' => 'Distinctive color for your display name across profile, chat, and rankings.',
                    ],
                    'price_coins'      => $nc['price'],
                    'rarity'           => $nc['rarity'],
                    'payload'          => [
                        'color' => $nc['color'],
                    ],
                    'is_consumable'    => false,
                    'max_quantity'     => 1,
                    'duration_minutes' => null,
                    'is_active'        => true,
                    'sort_order'       => 30,
                ]
            );
        }

        // 7. Sayt urg'u rangi palitralari (Accents)
        $accents = [
            [
                'key'       => 'accent_emerald',
                'name_uz'   => 'Zumrad bog‘i palitrasi',
                'name_ru'   => 'Изумрудная палитра',
                'name_en'   => 'Emerald Garden Accent',
                'hex'       => '#10B981',
                'price'     => 75,
                'rarity'    => 'common',
            ],
            [
                'key'       => 'accent_amber',
                'name_uz'   => 'Qadimiy qahrabo palitrasi',
                'name_ru'   => 'Янтарная палитра',
                'name_en'   => 'Ancient Amber Accent',
                'hex'       => '#F59E0B',
                'price'     => 75,
                'rarity'    => 'common',
            ],
            [
                'key'       => 'accent_rose',
                'name_uz'   => 'Kiber atirgul palitrasi',
                'name_ru'   => 'Кибер-розовая палитра',
                'name_en'   => 'Cyber Rose Accent',
                'hex'       => '#F43F5E',
                'price'     => 90,
                'rarity'    => 'rare',
            ],
            [
                'key'       => 'accent_violet',
                'name_uz'   => 'Kosmik binafsha palitrasi',
                'name_ru'   => 'Космическая фиолетовая палитра',
                'name_en'   => 'Cosmic Violet Accent',
                'hex'       => '#8B5CF6',
                'price'     => 90,
                'rarity'    => 'rare',
            ],
        ];

        foreach ($accents as $acc) {
            $this->seedItem(
                ['key' => $acc['key']],
                [
                    'category'         => 'colors',
                    'slot'             => 'accent',
                    'name'             => [
                        'uz' => $acc['name_uz'],
                        'ru' => $acc['name_ru'],
                        'en' => $acc['name_en'],
                    ],
                    'description'      => [
                        'uz' => 'Platformadagi tugmalar, chiziqlar va faol elementlarning shaxsiy urg‘u rangi.',
                        'ru' => 'Персональный акцентный цвет кнопок, линий и активных элементов платформы.',
                        'en' => 'Custom accent color for buttons, borders, and active platform elements.',
                    ],
                    'price_coins'      => $acc['price'],
                    'rarity'           => $acc['rarity'],
                    'payload'          => [
                        'color' => $acc['hex'],
                    ],
                    'is_consumable'    => false,
                    'max_quantity'     => 1,
                    'duration_minutes' => null,
                    'is_active'        => true,
                    'sort_order'       => 40,
                ]
            );
        }

        // 8. O'quvchi fon palitralari (Reader Palette)
        $readerPalettes = [
            [
                'key'        => 'reader_sepia_deluxe',
                'name_uz'    => 'Klassik Sepiya foni',
                'name_ru'    => 'Классическая Сепия',
                'name_en'    => 'Classic Sepia Reader',
                'bg_light'   => '#FBF0D9',
                'bg_dark'    => '#2C2216',
                'text_light' => '#5F4B32',
                'text_dark'  => '#EFE4CD',
                'price'      => 70,
                'rarity'     => 'common',
            ],
            [
                'key'        => 'reader_midnight_black',
                'name_uz'    => 'Chuqur OLED Tungi fon',
                'name_ru'    => 'Глубокий OLED Ночной фон',
                'name_en'    => 'Midnight OLED Black',
                'bg_light'   => '#121212',
                'bg_dark'    => '#000000',
                'text_light' => '#D1D5DB',
                'text_dark'  => '#E5E7EB',
                'price'      => 85,
                'rarity'     => 'rare',
            ],
            [
                'key'        => 'reader_parchment',
                'name_uz'    => 'Qirollik Pergamoni',
                'name_ru'    => 'Королевский Пергамент',
                'name_en'    => 'Royal Parchment',
                'bg_light'   => '#F5EBE1',
                'bg_dark'    => '#241E1A',
                'text_light' => '#44352A',
                'text_dark'  => '#E8DED6',
                'price'      => 90,
                'rarity'     => 'rare',
            ],
        ];

        foreach ($readerPalettes as $rp) {
            $this->seedItem(
                ['key' => $rp['key']],
                [
                    'category'         => 'colors',
                    'slot'             => 'reader_palette',
                    'name'             => [
                        'uz' => $rp['name_uz'],
                        'ru' => $rp['name_ru'],
                        'en' => $rp['name_en'],
                    ],
                    'description'      => [
                        'uz' => 'Kitob mutolaasi paytida ko‘zni toliqtirmaydigan maxsus o‘quvchi fon va matn palitrasi.',
                        'ru' => 'Комфортная палитра фона и текста для чтения книг без усталости глаз.',
                        'en' => 'Comfortable background and text palette for prolonged reading sessions.',
                    ],
                    'price_coins'      => $rp['price'],
                    'rarity'           => $rp['rarity'],
                    'payload'          => [
                        'bg_light'   => $rp['bg_light'],
                        'bg_dark'    => $rp['bg_dark'],
                        'text_light' => $rp['text_light'],
                        'text_dark'  => $rp['text_dark'],
                    ],
                    'is_consumable'    => false,
                    'max_quantity'     => 1,
                    'duration_minutes' => null,
                    'is_active'        => true,
                    'sort_order'       => 50,
                ]
            );
        }

        // 9. Matn belgilash ranglari (Highlight Color)
        $highlights = [
            [
                'key'     => 'hl_neon_lime',
                'name_uz' => 'Limon neon belgilagich',
                'name_ru' => 'Лимонный неоновый маркер',
                'name_en' => 'Neon Lime Highlighter',
                'color'   => '#84CC16',
                'price'   => 40,
                'rarity'  => 'common',
            ],
            [
                'key'     => 'hl_coral',
                'name_uz' => 'Marjon pushti belgilagich',
                'name_ru' => 'Кораллово-розовый маркер',
                'name_en' => 'Coral Rose Highlighter',
                'color'   => '#FB7185',
                'price'   => 40,
                'rarity'  => 'common',
            ],
            [
                'key'     => 'hl_cyan',
                'name_uz' => 'Lazer feruza belgilagich',
                'name_ru' => 'Лазерный бирюзовый маркер',
                'name_en' => 'Laser Cyan Highlighter',
                'color'   => '#06B6D4',
                'price'   => 40,
                'rarity'  => 'common',
            ],
        ];

        foreach ($highlights as $hl) {
            $this->seedItem(
                ['key' => $hl['key']],
                [
                    'category'         => 'colors',
                    'slot'             => 'highlight_color',
                    'name'             => [
                        'uz' => $hl['name_uz'],
                        'ru' => $hl['name_ru'],
                        'en' => $hl['name_en'],
                    ],
                    'description'      => [
                        'uz' => 'Kitob mutolaasi paytida matnlarni iqtibosga olish va belgilash uchun maxsus qalam rangi.',
                        'ru' => 'Цвет маркера для выделения цитат и заметок при чтении книг.',
                        'en' => 'Custom highlight marker color for book quotes and notes.',
                    ],
                    'price_coins'      => $hl['price'],
                    'rarity'           => $hl['rarity'],
                    'payload'          => [
                        'color' => $hl['color'],
                    ],
                    'is_consumable'    => false,
                    'max_quantity'     => 1,
                    'duration_minutes' => null,
                    'is_active'        => true,
                    'sort_order'       => 60,
                ]
            );
        }

        // 10. Imtiyozlar (Perks)
        $perks = [
            [
                'key'              => 'ai_questions_pack',
                'category'         => 'perk',
                'slot'             => null,
                'name_uz'          => 'AI savollar paketi (+10)',
                'name_ru'          => 'Пакет AI-вопросов (+10)',
                'name_en'          => 'AI Questions Pack (+10)',
                'desc_uz'          => 'Kitob tahlili AI yordamchisida kunlik limit tugaganda ishlatiladigan 10 ta qo‘shimcha aqlli savol.',
                'desc_ru'          => '10 дополнительных вопросов к ИИ-ассистенту после исчерпания дневного лимита.',
                'desc_en'          => '10 additional questions to the AI book assistant after reaching daily limit.',
                'price'            => 45,
                'rarity'           => 'rare',
                'is_consumable'    => true,
                'max_quantity'     => 5,
                'payload'          => ['effect' => 'ai_questions', 'amount' => 10, 'icon' => 'sparkles'],
                'sort_order'       => 70,
            ],
            [
                'key'              => 'extra_group_slot',
                'category'         => 'perk',
                'slot'             => null,
                'name_uz'          => 'Qo‘shimcha guruh o‘rni (+1)',
                'name_ru'          => 'Дополнительное место в группе (+1)',
                'name_en'          => 'Extra Group Slot (+1)',
                'desc_uz'          => 'Kitobxonlik klublari va guruhlariga a’zo bo‘lish chegarangizni yana 1 taga oshiring.',
                'desc_ru'          => 'Увеличивает ваш лимит участия в читательских клубах и группах на 1 место.',
                'desc_en'          => 'Expands your membership limit for reading clubs and groups by 1 slot.',
                'price'            => 120,
                'rarity'           => 'epic',
                'is_consumable'    => false,
                'max_quantity'     => 3,
                'payload'          => ['effect' => 'group_slot', 'amount' => 1, 'icon' => 'users'],
                'sort_order'       => 71,
            ],
            [
                'key'              => 'live_priority_question',
                'category'         => 'perk',
                'slot'             => null,
                'name_uz'          => 'Jonli efirda ustuvor savol',
                'name_ru'          => 'Приоритетный вопрос в эфире',
                'name_en'          => 'Live Stream Priority Question',
                'desc_uz'          => 'Jonli efirda ustozga yo‘llangan savolingiz alohida oltin nishon bilan birinchi qatorga ko‘tariladi.',
                'desc_ru'          => 'Ваш вопрос лектору на прямом эфире выделяется золотым бейджем и поднимается в топ.',
                'desc_en'          => 'Highlights your question with a golden VIP badge and elevates it during live broadcasts.',
                'price'            => 60,
                'rarity'           => 'rare',
                'is_consumable'    => true,
                'max_quantity'     => 5,
                'payload'          => ['effect' => 'live_priority', 'icon' => 'radio'],
                'sort_order'       => 72,
            ],
        ];

        foreach ($perks as $p) {
            $this->seedItem(
                ['key' => $p['key']],
                [
                    'category'         => $p['category'],
                    'slot'             => $p['slot'],
                    'name'             => [
                        'uz' => $p['name_uz'],
                        'ru' => $p['name_ru'],
                        'en' => $p['name_en'],
                    ],
                    'description'      => [
                        'uz' => $p['desc_uz'],
                        'ru' => $p['desc_ru'],
                        'en' => $p['desc_en'],
                    ],
                    'price_coins'      => $p['price'],
                    'rarity'           => $p['rarity'],
                    'payload'          => $p['payload'],
                    'is_consumable'    => $p['is_consumable'],
                    'max_quantity'     => $p['max_quantity'],
                    'duration_minutes' => null,
                    'is_active'        => true,
                    'sort_order'       => $p['sort_order'],
                ]
            );
        }
    }
}
