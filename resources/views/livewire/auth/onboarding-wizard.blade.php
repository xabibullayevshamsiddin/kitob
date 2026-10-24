@php
    $currentLoc = $preferred_locale ?? ($this->preferred_locale ?? app()->getLocale());
    $lang = in_array($currentLoc, ['uz', 'ru', 'en'], true) ? $currentLoc : 'uz';

    $t = [
        'uz' => [
            'step_label'       => 'Qadam',
            'skip_btn'         => "O'tkazib yuborish →",
            'prev_btn'         => '← Ortga',
            'next_btn'         => 'Keyingi qadam →',
            'finish_btn'       => $isEditMode ? 'Sozlamalarni saqlash ✨' : 'Platformaga kirish (+50 ball & +25 🪙)',
            'step_names'       => [
                1 => "Til va O'qish joyi",
                2 => 'Asosiy maqsad',
                3 => 'Janr va Format',
                4 => 'Kunlik reja',
                5 => 'Profil va Bonus',
            ],
            'lang_section_title' => 'Platforma tilini tanlang',
            'read_lang_title'    => 'Qaysi tillarda kitob o\'qishni yoqtirasiz?',
            'read_langs'         => [
                'uz'    => "🇺🇿 O'zbek tilida",
                'ru'    => '🇷🇺 Rus tilida',
                'en'    => '🇬🇧 Ingliz tilida',
                'world' => '🌍 Jahon adabiyoti',
            ],
            's1_title' => "Kitoblarni ko'pincha qayerda o'qiysiz?",
            's1_sub'   => "Bu sizga mos mutolaa ritmi va muhitini shakllantirishga yordam beradi",
            'places'   => [
                ['id' => 'home',       'icon' => '🏡', 'label' => 'Uyda',               'desc' => 'Tinch va osoyishta muhitda'],
                ['id' => 'office',     'icon' => '💼', 'label' => 'Ishxonada',          'desc' => "Tanaffus va bo'sh vaqtda"],
                ['id' => 'university', 'icon' => '🎓', 'label' => "O'quv dargohida",    'desc' => 'Universitet yoki maktabda'],
                ['id' => 'library',    'icon' => '📚', 'label' => 'Kutubxonada',        'desc' => 'Fokuslangan ilmiy muhitda'],
                ['id' => 'travel',     'icon' => '🎧', 'label' => "Yo'lda",             'desc' => 'Transport, metro yoki safarda'],
                ['id' => 'cafe',       'icon' => '☕', 'label' => "Qahvaxona / Bog'da", 'desc' => 'Ochiq havo va sokin burchakda'],
                ['id' => 'bedtime',    'icon' => '🌙', 'label' => 'Uyqu oldidan',       'desc' => 'Kechki sokin mutolaa odati'],
                ['id' => 'other',      'icon' => '✨', 'label' => 'Boshqa joyda',       'desc' => 'Har xil qulay joylarda'],
            ],
            's2_title' => "Kitob o'qishdan asosiy maqsadingiz nima?",
            's2_sub'   => 'Maqsadingizga qarab sun\'iy intellekt va ustozlar sizga eng mos kitoblarni tavsiya qiladi',
            'goals'    => [
                ['id' => 'knowledge',         'icon' => '💡', 'label' => 'Yangi bilim va dunyoqarash',       'desc' => "Ilm-fan, tarix va keng dunyoqarash"],
                ['id' => 'personal_dev',      'icon' => '🚀', 'label' => 'Shaxsiy rivojlanish va intizom',   'desc' => 'Vaqtni boshqarish, odatlar va motivatsiya'],
                ['id' => 'career',            'icon' => '📈', 'label' => 'Kasbiy va biznes o\'sish',         'desc' => 'Liderlik, moliya va kasbiy mahorat'],
                ['id' => 'language',          'icon' => '🗣️', 'label' => "Til o'rganish va so'z boyligi",    'desc' => 'Chiroyli nutq va boy lug\'at zaxirasi'],
                ['id' => 'exam_prep',         'icon' => '🎯', 'label' => 'Imtihon va testlarga tayyorgarlik','desc' => 'Yuqori akademik natijalar va testlar'],
                ['id' => 'critical_thinking', 'icon' => '🧠', 'label' => 'Tanqidiy fikrlash va tahlil',      'desc' => 'Chuqur mantiq va tahliliy tafakkur'],
                ['id' => 'spiritual',         'icon' => '🌿', 'label' => 'Ruhiy xotirjamlik va badiiy zavq', 'desc' => "Stressdan xalos bo'lish va ma'naviy ozuqa"],
                ['id' => 'other',             'icon' => '🌟', 'label' => 'Boshqa shaxsiy maqsad',            'desc' => "O'z qiziqishlarim bo'yicha erkin mutolaa"],
            ],
            's3_title'       => 'Sevimli janrlaringiz va mutolaa formati',
            's3_sub'         => 'Kamida 1 ta (maksimum 5 ta) janr tanlang — katalog siz uchun moslashtiriladi',
            'selected_count' => 'Tanlandi',
            'genres'         => [
                ['id' => 'fiction',    'icon' => '📖', 'label' => 'Badiiy adabiyot',     'desc' => 'Romanlar va qissalar'],
                ['id' => 'self_help',  'icon' => '🚀', 'label' => 'Shaxsiy rivojlanish', 'desc' => 'Odatlar va muvaffaqiyat'],
                ['id' => 'business',   'icon' => '💼', 'label' => 'Biznes va Moliya',    'desc' => 'Tadbirkorlik va boshqaruv'],
                ['id' => 'psychology', 'icon' => '🧠', 'label' => 'Psixologiya',         'desc' => 'Inson ongi va munosabatlar'],
                ['id' => 'history',    'icon' => '🏛️', 'label' => 'Tarix va Biografiya', 'desc' => 'Buyuk shaxslar va davrlar'],
                ['id' => 'science',    'icon' => '🔬', 'label' => 'Ilm-fan va IT',       'desc' => 'Zamonaviy texnologiyalar'],
                ['id' => 'detective',  'icon' => '🕵️', 'label' => 'Detektiv va Fantastika','desc' => 'Sirli voqealar va kelajak'],
                ['id' => 'classic',    'icon' => '📜', 'label' => 'Mumtoz va Sharq',     'desc' => "O'zbek va jahon durdonalari"],
            ],
            'format_title' => 'Sizga qaysi formatda o\'rganish qulayroq?',
            'formats'      => [
                ['id' => '3d_pdf',     'icon' => '📕', 'label' => '3D Kitob va PDF',      'desc' => 'Haqiqiy kitobdek varaqlab o\'qish'],
                ['id' => 'audio',      'icon' => '🎧', 'label' => 'Audio mutolaa',        'desc' => "Yo'lda va ishda tinglab o'rganish"],
                ['id' => 'video_quiz', 'icon' => '🎬', 'label' => 'Video tahlil + Test',  'desc' => 'Video darslar va bilimlarni sinash'],
                ['id' => 'all',        'icon' => '⚡', 'label' => 'Barchasi birgalikda',  'desc' => 'Matn + Audio + Video + AI yordamchi'],
            ],
            's4_title'   => 'Kunlik mutolaa rejasi va Qulay rejim',
            's4_sub'     => 'Kichik kunlik odat yil davomida ulkan natijaga aylanadi',
            'daily_plans'=> [
                ['min' => 15, 'icon' => '🌱', 'label' => '15 daqiqa / kun', 'badge' => 'Yiliga ~12 kitob', 'desc' => 'Yengil boshlash — kuniga 6–8 sahifa'],
                ['min' => 30, 'icon' => '🔥', 'label' => '30 daqiqa / kun', 'badge' => 'Tavsiya etiladi',  'desc' => 'Oltin o\'rtalik — yiliga ~24 ta kitob'],
                ['min' => 60, 'icon' => '⚡', 'label' => '60 daqiqa / kun', 'badge' => '+200 ball & +40 🪙','desc' => 'Har kuni 1 soat o\'qiganga Super Bonus beriladi!'],
                ['min' => 90, 'icon' => '👑', 'label' => '90+ daqiqa / kun','badge' => 'Yiliga ~70+ kitob','desc' => 'Chuqur akademik mutolaa va Top-5 liderlik'],
            ],
            'theme_title' => "Ko'z uchun qulay mutolaa mavzusi",
            'themes'      => [
                ['id' => 'light', 'icon' => '☀️', 'label' => "Yorug' krem", 'desc' => 'Kunduzgi mutolaa'],
                ['id' => 'sepia', 'icon' => '📜', 'label' => 'Klassik Sepia', 'desc' => "Ko'zni charchatmaydi"],
                ['id' => 'dark',  'icon' => '🌙', 'label' => 'Tungi rejim',   'desc' => 'Kechki sokin mutolaa'],
            ],
            'notif_title' => 'Foydali eslatmalar (Telegram & Sayt)',
            'notif_streak'=> '🔥 Kunlik streakni yo\'qotmaslik eslatmasi',
            'notif_quote' => '✨ Kunlik hikmat va sara iqtiboslar',
            'notif_live'  => '🔴 Jonli efir va muhokamalar boshlanishi',
            's5_title'    => $isEditMode ? 'Profilingiz va Mutolaa pasportingiz' : 'Deyarli tayyor! Xush kelibsiz sovg\'asi 🎁',
            's5_sub'      => $isEditMode
                ? 'Ma\'lumotlaringizni tekshiring va saqlang'
                : 'Yakunlang va darhol +50 xush kelibsiz balli hamda +25 tanga 🪙 mukofotiga ega bo\'ling!',
            'avatar_label'=> 'Profil suratingiz yoki Kitobxon timsoli',
            'upload_hint' => 'Rasm yuklash (PNG/JPG, 2MB gacha)',
            'uploading'   => 'Rasm yuklanmoqda...',
            'preset_label'=> 'Yoki tezkor timsol tanlang:',
            'fav_book_lbl'=> 'Sevimli kitobingiz yoki shioringiz (ixtiyoriy)',
            'fav_book_ph' => "Masalan: «O'tkan kunlar», «Atom odatlar»...",
            'bio_label'   => "O'zingiz haqingizda qisqacha (ixtiyoriy)",
            'bio_ph'      => 'Sevimli janrlaringiz, kasbingiz yoki maqsadingiz haqida 1–2 jumla...',
            'passport_title' => '📋 Sizning Shaxsiy Mutolaa Pasportingiz',
            'summary_lang'   => 'Til',
            'summary_place'  => 'Mutolaa joyi',
            'summary_goal'   => 'Maqsad',
            'summary_plan'   => 'Kunlik reja',
            'min_per_day'    => 'daq/kun',
        ],
        'ru' => [
            'step_label'       => 'Шаг',
            'skip_btn'         => 'Пропустить →',
            'prev_btn'         => '← Назад',
            'next_btn'         => 'Далее →',
            'finish_btn'       => $isEditMode ? 'Сохранить настройки ✨' : 'Войти на платформу (+50 баллов и +25 🪙)',
            'step_names'       => [
                1 => 'Язык и Место',
                2 => 'Главная цель',
                3 => 'Жанры и Формат',
                4 => 'План чтения',
                5 => 'Профиль и Бонус',
            ],
            'lang_section_title' => 'Выберите язык интерфейса',
            'read_lang_title'    => 'На каких языках вы предпочитаете читать книги?',
            'read_langs'         => [
                'uz'    => '🇺🇿 На узбекском',
                'ru'    => '🇷🇺 На русском',
                'en'    => '🇬🇧 На английском',
                'world' => '🌍 Мировая литература',
            ],
            's1_title' => 'Где вы чаще всего читаете книги?',
            's1_sub'   => 'Это поможет подобрать комфортный ритм и режим чтения специально для вас',
            'places'   => [
                ['id' => 'home',       'icon' => '🏡', 'label' => 'Дома',                'desc' => 'В тихой и уютной обстановке'],
                ['id' => 'office',     'icon' => '💼', 'label' => 'На работе',           'desc' => 'В перерывах и свободное время'],
                ['id' => 'university', 'icon' => '🎓', 'label' => 'На учебе',            'desc' => 'В университете или школе'],
                ['id' => 'library',    'icon' => '📚', 'label' => 'В библиотеке',        'desc' => 'В атмосфере глубокого фокуса'],
                ['id' => 'travel',     'icon' => '🎧', 'label' => 'В дороге',            'desc' => 'В метро, транспорте или поездках'],
                ['id' => 'cafe',       'icon' => '☕', 'label' => 'В кафе / парке',      'desc' => 'На свежем воздухе за чашкой кофе'],
                ['id' => 'bedtime',    'icon' => '🌙', 'label' => 'Перед сном',          'desc' => 'Вечерний ритуал спокойного чтения'],
                ['id' => 'other',      'icon' => '✨', 'label' => 'В другом месте',      'desc' => 'В любых удобных местах'],
            ],
            's2_title' => 'Какова ваша главная цель чтения?',
            's2_sub'   => 'ИИ-помощник и наставники подберут лучшие книги под вашу цель',
            'goals'    => [
                ['id' => 'knowledge',         'icon' => '💡', 'label' => 'Новые знания и кругозор',          'desc' => 'Наука, история и широкое мышление'],
                ['id' => 'personal_dev',      'icon' => '🚀', 'label' => 'Личностный рост и дисциплина',     'desc' => 'Привычки, тайм-менеджмент и мотивация'],
                ['id' => 'career',            'icon' => '📈', 'label' => 'Карьера и развитие бизнеса',       'desc' => 'Лидерство, финансы и мастерство'],
                ['id' => 'language',          'icon' => '🗣️', 'label' => 'Изучение языков и словарный запас','desc' => 'Красивая речь и богатая лексика'],
                ['id' => 'exam_prep',         'icon' => '🎯', 'label' => 'Подготовка к экзаменам и тестам',  'desc' => 'Высокие академические результаты'],
                ['id' => 'critical_thinking', 'icon' => '🧠', 'label' => 'Критическое мышление и анализ',    'desc' => 'Глубокая логика и аналитика'],
                ['id' => 'spiritual',         'icon' => '🌿', 'label' => 'Душевное спокойствие и эстетика',  'desc' => 'Снятие стресса и вдохновение'],
                ['id' => 'other',             'icon' => '🌟', 'label' => 'Другая личная цель',               'desc' => 'Свободное чтение по интересам'],
            ],
            's3_title'       => 'Любимые жанры и формат чтения',
            's3_sub'         => 'Выберите от 1 до 5 жанров — мы настроим персональные рекомендации',
            'selected_count' => 'Выбрано',
            'genres'         => [
                ['id' => 'fiction',    'icon' => '📖', 'label' => 'Художественная',      'desc' => 'Романы, повести и рассказы'],
                ['id' => 'self_help',  'icon' => '🚀', 'label' => 'Саморазвитие',        'desc' => 'Привычки и эффективность'],
                ['id' => 'business',   'icon' => '💼', 'label' => 'Бизнес и Финансы',    'desc' => 'Предпринимательство и управление'],
                ['id' => 'psychology', 'icon' => '🧠', 'label' => 'Психология',          'desc' => 'Мышление и отношения'],
                ['id' => 'history',    'icon' => '🏛️', 'label' => 'История и Биографии', 'desc' => 'Великие личности и эпохи'],
                ['id' => 'science',    'icon' => '🔬', 'label' => 'Наука и Технологии',  'desc' => 'Будущее, ИИ и открытия'],
                ['id' => 'detective',  'icon' => '🕵️', 'label' => 'Детективы и Фантастика','desc' => 'Загадки и приключения'],
                ['id' => 'classic',    'icon' => '📜', 'label' => 'Мировая классика',    'desc' => 'Шедевры восточной и мировой прозы'],
            ],
            'format_title' => 'Какой формат обучения вам удобнее?',
            'formats'      => [
                ['id' => '3d_pdf',     'icon' => '📕', 'label' => '3D Книга и PDF',       'desc' => 'Реалистичное перелистывание страниц'],
                ['id' => 'audio',      'icon' => '🎧', 'label' => 'Аудиокниги',           'desc' => 'Удобно слушать в дороге и делах'],
                ['id' => 'video_quiz', 'icon' => '🎬', 'label' => 'Видеоразборы + Тесты', 'desc' => 'Видеоуроки и проверка знаний'],
                ['id' => 'all',        'icon' => '⚡', 'label' => 'Все форматы вместе',   'desc' => 'Текст + Аудио + Видео + ИИ-ассистент'],
            ],
            's4_title'   => 'Ежедневный план и Режим чтения',
            's4_sub'     => 'Маленькая ежедневная привычка приводит к большим результатам за год',
            'daily_plans'=> [
                ['min' => 15, 'icon' => '🌱', 'label' => '15 минут / день', 'badge' => '~12 книг в год',   'desc' => 'Легкий старт — 6–8 страниц в день'],
                ['min' => 30, 'icon' => '🔥', 'label' => '30 минут / день', 'badge' => 'Рекомендуется',    'desc' => 'Золотая середина — ~24 книги в год'],
                ['min' => 60, 'icon' => '⚡', 'label' => '60 минут / день', 'badge' => '+200 баллов и +40 🪙','desc' => 'Ежедневный Супер-бонус за 1 час чтения!'],
                ['min' => 90, 'icon' => '👑', 'label' => '90+ минут / день','badge' => '~70+ книг в год',  'desc' => 'Глубокое погружение и лидерство в Топ-5'],
            ],
            'theme_title' => 'Комфортная тема оформления',
            'themes'      => [
                ['id' => 'light', 'icon' => '☀️', 'label' => 'Светлая',       'desc' => 'Теплая бумага днем'],
                ['id' => 'sepia', 'icon' => '📜', 'label' => 'Сепия',         'desc' => 'Защита для глаз'],
                ['id' => 'dark',  'icon' => '🌙', 'label' => 'Темная ночная', 'desc' => 'Для вечернего чтения'],
            ],
            'notif_title' => 'Умные уведомления (Telegram и Сайт)',
            'notif_streak'=> '🔥 Напоминание о сохранении ежедневного стрика',
            'notif_quote' => '✨ Цитата дня и полезные инсайты из книг',
            'notif_live'  => '🔴 Уведомления о начале прямых эфиров',
            's5_title'    => $isEditMode ? 'Ваш профиль и Паспорт читателя' : 'Почти готово! Приветственный подарок 🎁',
            's5_sub'      => $isEditMode
                ? 'Проверьте и сохраните обновленные настройки'
                : 'Завершите настройку и сразу получите +50 баллов и +25 монет 🪙 на баланс!',
            'avatar_label'=> 'Фото профиля или Символ читателя',
            'upload_hint' => 'Загрузить фото (PNG/JPG, до 2MB)',
            'uploading'   => 'Загрузка изображения...',
            'preset_label'=> 'Или выберите символ:',
            'fav_book_lbl'=> 'Любимая книга или девиз (необязательно)',
            'fav_book_ph' => 'Например: «Минувшие дни», «Атомные привычки»...',
            'bio_label'   => 'Коротко о себе (необязательно)',
            'bio_ph'      => '1–2 предложения о ваших интересах, профессии или целях...',
            'passport_title' => '📋 Ваш Персональный Паспорт Читателя',
            'summary_lang'   => 'Язык',
            'summary_place'  => 'Место чтения',
            'summary_goal'   => 'Цель',
            'summary_plan'   => 'Дневная цель',
            'min_per_day'    => 'мин/день',
        ],
        'en' => [
            'step_label'       => 'Step',
            'skip_btn'         => 'Skip for now →',
            'prev_btn'         => '← Back',
            'next_btn'         => 'Next step →',
            'finish_btn'       => $isEditMode ? 'Save Preferences ✨' : 'Enter Platform (+50 pts & +25 🪙)',
            'step_names'       => [
                1 => 'Language & Place',
                2 => 'Main Goal',
                3 => 'Genres & Format',
                4 => 'Daily Plan',
                5 => 'Profile & Bonus',
            ],
            'lang_section_title' => 'Choose interface language',
            'read_lang_title'    => 'Which languages do you enjoy reading in?',
            'read_langs'         => [
                'uz'    => '🇺🇿 Uzbek',
                'ru'    => '🇷🇺 Russian',
                'en'    => '🇬🇧 English',
                'world' => '🌍 World Literature',
            ],
            's1_title' => 'Where do you usually read books?',
            's1_sub'   => 'This helps us tailor your reading rhythm and environment',
            'places'   => [
                ['id' => 'home',       'icon' => '🏡', 'label' => 'At Home',           'desc' => 'In a calm and cozy space'],
                ['id' => 'office',     'icon' => '💼', 'label' => 'At Work',           'desc' => 'During breaks and free time'],
                ['id' => 'university', 'icon' => '🎓', 'label' => 'At Campus / School','desc' => 'Between classes and study sessions'],
                ['id' => 'library',    'icon' => '📚', 'label' => 'In the Library',    'desc' => 'Deeply focused academic setting'],
                ['id' => 'travel',     'icon' => '🎧', 'label' => 'On the Go',         'desc' => 'Commuting, metro, or traveling'],
                ['id' => 'cafe',       'icon' => '☕', 'label' => 'Cafe / Park',       'desc' => 'Outdoors or over a cup of coffee'],
                ['id' => 'bedtime',    'icon' => '🌙', 'label' => 'Before Sleep',      'desc' => 'Peaceful evening reading habit'],
                ['id' => 'other',      'icon' => '✨', 'label' => 'Somewhere Else',    'desc' => 'Wherever inspiration strikes'],
            ],
            's2_title' => 'What is your primary reading goal?',
            's2_sub'   => 'Our AI assistant and mentors will recommend books matched to your goal',
            'goals'    => [
                ['id' => 'knowledge',         'icon' => '💡', 'label' => 'New Knowledge & Horizons',      'desc' => 'Science, history, and broad thinking'],
                ['id' => 'personal_dev',      'icon' => '🚀', 'label' => 'Self-Improvement & Discipline', 'desc' => 'Habits, time management, motivation'],
                ['id' => 'career',            'icon' => '📈', 'label' => 'Career & Business Growth',      'desc' => 'Leadership, finance, professional skills'],
                ['id' => 'language',          'icon' => '🗣️', 'label' => 'Language & Vocabulary',         'desc' => 'Fluency, eloquence, and expression'],
                ['id' => 'exam_prep',         'icon' => '🎯', 'label' => 'Exam & Test Preparation',       'desc' => 'High academic scores and quizzes'],
                ['id' => 'critical_thinking', 'icon' => '🧠', 'label' => 'Critical Thinking & Analysis',  'desc' => 'Deep logic and analytical mindset'],
                ['id' => 'spiritual',         'icon' => '🌿', 'label' => 'Inner Peace & Literary Joy',    'desc' => 'Stress relief and mindfulness'],
                ['id' => 'other',             'icon' => '🌟', 'label' => 'Another Personal Goal',         'desc' => 'Exploring at my own pace'],
            ],
            's3_title'       => 'Favorite Genres & Reading Format',
            's3_sub'         => 'Pick 1 to 5 genres to personalize your book catalog',
            'selected_count' => 'Selected',
            'genres'         => [
                ['id' => 'fiction',    'icon' => '📖', 'label' => 'Fiction & Novels',    'desc' => 'Stories and literary masterpieces'],
                ['id' => 'self_help',  'icon' => '🚀', 'label' => 'Self-Improvement',    'desc' => 'Habits, mindset, productivity'],
                ['id' => 'business',   'icon' => '💼', 'label' => 'Business & Finance',  'desc' => 'Entrepreneurship & leadership'],
                ['id' => 'psychology', 'icon' => '🧠', 'label' => 'Psychology',          'desc' => 'Human mind & relationships'],
                ['id' => 'history',    'icon' => '🏛️', 'label' => 'History & Biography', 'desc' => 'Great leaders and eras'],
                ['id' => 'science',    'icon' => '🔬', 'label' => 'Science & Tech',      'desc' => 'Innovation, AI, and discovery'],
                ['id' => 'detective',  'icon' => '🕵️', 'label' => 'Mystery & Sci-Fi',    'desc' => 'Thrillers and future worlds'],
                ['id' => 'classic',    'icon' => '📜', 'label' => 'World Classics',      'desc' => 'Timeless Eastern & Western works'],
            ],
            'format_title' => 'Which learning format suits you best?',
            'formats'      => [
                ['id' => '3d_pdf',     'icon' => '📕', 'label' => '3D Flipbook & PDF',  'desc' => 'Realistic page-turning experience'],
                ['id' => 'audio',      'icon' => '🎧', 'label' => 'Audiobooks',         'desc' => 'Listen while commuting or working'],
                ['id' => 'video_quiz', 'icon' => '🎬', 'label' => 'Video + Quizzes',    'desc' => 'Video summaries and interactive tests'],
                ['id' => 'all',        'icon' => '⚡', 'label' => 'All-in-One Experience','desc' => 'Text + Audio + Video + AI Assistant'],
            ],
            's4_title'   => 'Daily Reading Target & Theme',
            's4_sub'     => 'A small daily reading habit compounds into extraordinary annual results',
            'daily_plans'=> [
                ['min' => 15, 'icon' => '🌱', 'label' => '15 mins / day', 'badge' => '~12 books/yr',     'desc' => 'Easy start — 6–8 pages a day'],
                ['min' => 30, 'icon' => '🔥', 'label' => '30 mins / day', 'badge' => 'Recommended',      'desc' => 'Sweet spot — ~24 books a year'],
                ['min' => 60, 'icon' => '⚡', 'label' => '60 mins / day', 'badge' => '+200 pts & +40 🪙','desc' => 'Unlocks the Daily 1-Hour Super Bonus!'],
                ['min' => 90, 'icon' => '👑', 'label' => '90+ mins / day','badge' => '~70+ books/yr',    'desc' => 'Deep mastery & Top-5 Leaderboard pace'],
            ],
            'theme_title' => 'Eye-Friendly Reading Theme',
            'themes'      => [
                ['id' => 'light', 'icon' => '☀️', 'label' => 'Warm Light',   'desc' => 'Crisp cream paper'],
                ['id' => 'sepia', 'icon' => '📜', 'label' => 'Classic Sepia','desc' => 'Zero eye strain'],
                ['id' => 'dark',  'icon' => '🌙', 'label' => 'Night Dark',   'desc' => 'Cozy evening mode'],
            ],
            'notif_title' => 'Smart Reminders (Telegram & Web)',
            'notif_streak'=> '🔥 Daily streak protection reminder',
            'notif_quote' => '✨ Daily wisdom & book quotes',
            'notif_live'  => '🔴 Live stream & discussion alerts',
            's5_title'    => $isEditMode ? 'Your Profile & Reader Passport' : 'Almost Ready! Claim Your Welcome Gift 🎁',
            's5_sub'      => $isEditMode
                ? 'Review and save your updated reading preferences'
                : 'Finish setup and immediately receive +50 welcome points and +25 coins 🪙!',
            'avatar_label'=> 'Profile Photo or Reader Emblem',
            'upload_hint' => 'Upload photo (PNG/JPG, up to 2MB)',
            'uploading'   => 'Uploading image...',
            'preset_label'=> 'Or pick an emblem:',
            'fav_book_lbl'=> 'Favorite Book or Motto (optional)',
            'fav_book_ph' => 'e.g., "Bygone Days", "Atomic Habits"...',
            'bio_label'   => 'Short Bio (optional)',
            'bio_ph'      => '1–2 sentences about your favorite books, profession, or goals...',
            'passport_title' => '📋 Your Personal Reader Passport',
            'summary_lang'   => 'Language',
            'summary_place'  => 'Reading Place',
            'summary_goal'   => 'Main Goal',
            'summary_plan'   => 'Daily Target',
            'min_per_day'    => 'min/day',
        ],
    ][$lang];

    $avatarPresets = ['🦁', '🦅', '🦉', '🐺', '🦊', '🎓', '👑', '🚀'];
@endphp

<div class="space-y-6"
     x-data="{
         init() {
             window.addEventListener('onboarding-theme-selected', (e) => {
                 const th = e.detail?.theme;
                 if (!th) return;
                 try { localStorage.setItem('reader_theme', th); } catch (err) {}
                 if (th === 'light' || th === 'sepia') {
                     if (window.__setKitobTheme) window.__setKitobTheme('light');
                 } else {
                     if (window.__setKitobTheme) window.__setKitobTheme('dark');
                 }
             });
         }
     }">

    <!-- ── Top Header: Language Pills & Skip Button ── -->
    <div class="flex flex-wrap items-center justify-between gap-2 pb-3 border-b border-ink-border">
        <div class="flex items-center gap-1 bg-ink-950 p-1 rounded-btn border border-ink-border">
            @foreach([
                'uz' => ['flag' => 'uz', 'label' => "O'zbek"],
                'ru' => ['flag' => 'ru', 'label' => 'Русский'],
                'en' => ['flag' => 'gb', 'label' => 'English'],
            ] as $locCode => $locMeta)
                <button type="button"
                        wire:click="selectLocale('{{ $locCode }}')"
                        class="px-2.5 py-1 rounded-badge text-[11px] font-mono font-semibold flex items-center gap-1.5 transition-all {{ $preferred_locale === $locCode ? 'bg-amber-500 text-ink-950 font-bold shadow-sm' : 'text-mist hover:text-paper' }}">
                    <img src="https://flagcdn.com/20x15/{{ $locMeta['flag'] }}.png" width="14" height="10" alt="{{ $locCode }}" class="rounded-[1px]" onerror="this.style.display='none'">
                    <span>{{ $locMeta['label'] }}</span>
                </button>
            @endforeach
        </div>

        <button type="button"
                wire:click="skipOnboarding"
                class="text-[11px] font-mono text-mist hover:text-amber-600 dark:hover:text-amber-400 transition-colors">
            {{ $t['skip_btn'] }}
        </button>
    </div>

    <!-- ── Interactive 5-Step Progress Bar ── -->
    <div>
        <div class="flex items-center justify-between mb-2">
            <span class="text-xs font-bold text-amber-600 dark:text-amber-400 font-mono uppercase tracking-wider">
                {{ $t['step_label'] }} {{ $currentStep }} / {{ $totalSteps }}
            </span>
            <span class="text-xs font-semibold text-paper font-serif">
                {{ $t['step_names'][$currentStep] ?? '' }}
            </span>
        </div>

        <!-- Stepper Pills -->
        <div class="grid grid-cols-5 gap-1.5 mb-2">
            @for($s = 1; $s <= $totalSteps; $s++)
                <button type="button"
                        wire:click="goToStep({{ $s }})"
                        class="h-2 rounded-full transition-all duration-300 {{ $s < $currentStep ? 'bg-emerald-500' : ($s === $currentStep ? 'bg-amber-500 ring-2 ring-amber-500/30' : 'bg-ink-800 hover:bg-ink-700') }}"
                        title="{{ $t['step_names'][$s] ?? '' }}"></button>
            @endfor
        </div>
    </div>

    <!-- ══════════════════════════════════════════════════════════════════════
         STEP 1: Til va Mutolaa Joyi
    ═══════════════════════════════════════════════════════════════════════ -->
    @if ($currentStep === 1)
        <div class="space-y-5 animate-fade-in">
            <div class="text-center">
                <h2 class="text-xl sm:text-2xl font-extrabold font-serif text-paper tracking-tight">{{ $t['s1_title'] }}</h2>
                <p class="text-xs text-mist mt-1">{{ $t['s1_sub'] }}</p>
            </div>

            <!-- Reading Languages Multi-Select -->
            <div class="p-3.5 rounded-card bg-ink-950 border border-ink-border space-y-2">
                <span class="text-[11px] font-mono uppercase tracking-wider text-mist block font-semibold">
                    🌐 {{ $t['read_lang_title'] }}
                </span>
                <div class="flex flex-wrap gap-1.5">
                    @foreach($t['read_langs'] as $rCode => $rLabel)
                        @php $isLangSelected = in_array($rCode, $reading_languages, true); @endphp
                        <button type="button"
                                wire:click="toggleReadingLanguage('{{ $rCode }}')"
                                class="px-2.5 py-1.5 rounded-btn text-xs font-medium border transition-all flex items-center gap-1.5 {{ $isLangSelected ? 'bg-amber-500/15 border-amber-500 text-amber-700 dark:text-amber-300 font-bold' : 'bg-ink-900 border-ink-border text-mist hover:text-paper' }}">
                            <span>{{ $rLabel }}</span>
                            @if($isLangSelected)
                                <span class="text-amber-600 dark:text-amber-400 text-[10px]">✓</span>
                            @endif
                        </button>
                    @endforeach
                </div>
            </div>

            <!-- Reading Places Grid -->
            <div class="grid grid-cols-2 gap-2.5">
                @foreach ($t['places'] as $place)
                    <button type="button"
                            wire:click="selectPlace('{{ $place['id'] }}')"
                            class="p-3.5 rounded-2xl border text-left transition-all duration-200 transform hover:scale-[1.01] active:scale-[0.98] {{ $reading_place === $place['id'] ? 'bg-amber-500/15 border-amber-500 ring-1 ring-amber-500 shadow-sm' : 'bg-ink-950 border-ink-border hover:border-amber-500/40' }}">
                        <div class="flex items-center justify-between mb-1.5">
                            <span class="text-2xl">{{ $place['icon'] }}</span>
                            @if($reading_place === $place['id'])
                                <span class="w-5 h-5 rounded-full bg-amber-500 text-ink-950 text-xs font-bold flex items-center justify-center">✓</span>
                            @endif
                        </div>
                        <span class="text-xs sm:text-sm font-bold text-paper block leading-tight">{{ $place['label'] }}</span>
                        <span class="text-[11px] text-mist block mt-1 leading-snug">{{ $place['desc'] }}</span>
                    </button>
                @endforeach
            </div>

            @error('reading_place')
                <p class="text-rose-500 dark:text-rose-400 text-xs text-center">{{ $message }}</p>
            @enderror

            @if($reading_place)
                <div class="flex justify-end pt-1">
                    <button type="button" wire:click="nextStep" class="ks-btn-primary py-2 px-5 text-xs font-bold">
                        {{ $t['next_btn'] }}
                    </button>
                </div>
            @endif
        </div>
    @endif

    <!-- ══════════════════════════════════════════════════════════════════════
         STEP 2: Asosiy Maqsad
    ═══════════════════════════════════════════════════════════════════════ -->
    @if ($currentStep === 2)
        <div class="space-y-5 animate-fade-in">
            <div class="text-center">
                <h2 class="text-xl sm:text-2xl font-extrabold font-serif text-paper tracking-tight">{{ $t['s2_title'] }}</h2>
                <p class="text-xs text-mist mt-1">{{ $t['s2_sub'] }}</p>
            </div>

            <div class="space-y-2 max-h-[380px] overflow-y-auto pr-1">
                @foreach ($t['goals'] as $goal)
                    <button type="button"
                            wire:click="selectGoal('{{ $goal['id'] }}')"
                            class="w-full p-3.5 rounded-2xl border text-left flex items-center justify-between gap-3 transition-all duration-200 hover:scale-[1.01] active:scale-[0.99] {{ $reading_goal === $goal['id'] ? 'bg-amber-500/15 border-amber-500 ring-1 ring-amber-500' : 'bg-ink-950 border-ink-border hover:border-amber-500/40' }}">
                        <div class="flex items-center gap-3 min-w-0">
                            <span class="text-2xl shrink-0">{{ $goal['icon'] }}</span>
                            <div class="min-w-0">
                                <span class="text-xs sm:text-sm font-bold text-paper block leading-snug">{{ $goal['label'] }}</span>
                                <span class="text-[11px] text-mist block leading-snug mt-0.5">{{ $goal['desc'] }}</span>
                            </div>
                        </div>
                        <span class="w-6 h-6 rounded-full flex items-center justify-center shrink-0 {{ $reading_goal === $goal['id'] ? 'bg-amber-500 text-ink-950 font-bold text-xs' : 'bg-ink-900 text-mist text-xs' }}">
                            {{ $reading_goal === $goal['id'] ? '✓' : '→' }}
                        </span>
                    </button>
                @endforeach
            </div>

            <div class="flex items-center justify-between pt-2 border-t border-ink-border">
                <button type="button" wire:click="prevStep" class="ks-btn-ghost py-2 px-4 text-xs font-mono">
                    {{ $t['prev_btn'] }}
                </button>
                @if($reading_goal)
                    <button type="button" wire:click="nextStep" class="ks-btn-primary py-2 px-5 text-xs font-bold">
                        {{ $t['next_btn'] }}
                    </button>
                @endif
            </div>
        </div>
    @endif

    <!-- ══════════════════════════════════════════════════════════════════════
         STEP 3: Sevimli Janrlar va Mutolaa Formati
    ═══════════════════════════════════════════════════════════════════════ -->
    @if ($currentStep === 3)
        <div class="space-y-5 animate-fade-in">
            <div class="text-center">
                <h2 class="text-xl sm:text-2xl font-extrabold font-serif text-paper tracking-tight">{{ $t['s3_title'] }}</h2>
                <p class="text-xs text-mist mt-1">{{ $t['s3_sub'] }}</p>
            </div>

            <!-- Genres Multi-Select -->
            <div class="space-y-2">
                <div class="flex items-center justify-between text-xs font-mono">
                    <span class="text-mist uppercase tracking-wider">📚 {{ $t['step_names'][3] }}</span>
                    <span class="px-2 py-0.5 rounded-pill bg-amber-500/15 border border-amber-500/30 text-amber-700 dark:text-amber-400 font-bold">
                        {{ $t['selected_count'] }}: {{ count($favorite_genres) }} / 5
                    </span>
                </div>

                <div class="grid grid-cols-2 gap-2">
                    @foreach($t['genres'] as $g)
                        @php $isGenreSelected = in_array($g['id'], $favorite_genres, true); @endphp
                        <button type="button"
                                wire:click="toggleGenre('{{ $g['id'] }}')"
                                class="p-3 rounded-xl border text-left transition-all flex items-start gap-2.5 {{ $isGenreSelected ? 'bg-amber-500/15 border-amber-500 ring-1 ring-amber-500' : 'bg-ink-950 border-ink-border hover:border-amber-500/40' }}">
                            <span class="text-xl shrink-0">{{ $g['icon'] }}</span>
                            <div class="min-w-0 flex-1">
                                <div class="flex items-center justify-between gap-1">
                                    <span class="text-xs font-bold text-paper truncate">{{ $g['label'] }}</span>
                                    @if($isGenreSelected)
                                        <span class="text-amber-600 dark:text-amber-400 text-xs font-bold shrink-0">✓</span>
                                    @endif
                                </div>
                                <span class="text-[10px] text-mist block leading-tight mt-0.5">{{ $g['desc'] }}</span>
                            </div>
                        </button>
                    @endforeach
                </div>
            </div>

            <!-- Preferred Learning Format -->
            <div class="space-y-2 pt-2 border-t border-ink-border">
                <span class="text-xs font-mono uppercase tracking-wider text-mist block font-semibold">
                    🎧 {{ $t['format_title'] }}
                </span>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                    @foreach($t['formats'] as $fmt)
                        <button type="button"
                                wire:click="selectFormat('{{ $fmt['id'] }}')"
                                class="p-3 rounded-xl border text-left transition-all flex items-center gap-2.5 {{ $reading_format === $fmt['id'] ? 'bg-amber-500/15 border-amber-500 ring-1 ring-amber-500' : 'bg-ink-950 border-ink-border hover:border-amber-500/40' }}">
                            <span class="text-xl shrink-0">{{ $fmt['icon'] }}</span>
                            <div class="min-w-0">
                                <span class="text-xs font-bold text-paper block">{{ $fmt['label'] }}</span>
                                <span class="text-[10px] text-mist block leading-tight">{{ $fmt['desc'] }}</span>
                            </div>
                        </button>
                    @endforeach
                </div>
            </div>

            <div class="flex items-center justify-between pt-2 border-t border-ink-border">
                <button type="button" wire:click="prevStep" class="ks-btn-ghost py-2 px-4 text-xs font-mono">
                    {{ $t['prev_btn'] }}
                </button>
                <button type="button" wire:click="nextStep" class="ks-btn-primary py-2 px-5 text-xs font-bold">
                    {{ $t['next_btn'] }}
                </button>
            </div>
        </div>
    @endif

    <!-- ══════════════════════════════════════════════════════════════════════
         STEP 4: Kunlik Mutolaa Rejasi, Mavzu va Eslatmalar
    ═══════════════════════════════════════════════════════════════════════ -->
    @if ($currentStep === 4)
        <div class="space-y-5 animate-fade-in">
            <div class="text-center">
                <h2 class="text-xl sm:text-2xl font-extrabold font-serif text-paper tracking-tight">{{ $t['s4_title'] }}</h2>
                <p class="text-xs text-mist mt-1">{{ $t['s4_sub'] }}</p>
            </div>

            <!-- Daily Reading Minutes Goal -->
            <div class="space-y-2">
                @foreach($t['daily_plans'] as $plan)
                    <button type="button"
                            wire:click="selectDailyGoal({{ $plan['min'] }})"
                            class="w-full p-3.5 rounded-2xl border text-left flex items-center justify-between gap-3 transition-all {{ $daily_minutes_goal === $plan['min'] ? 'bg-amber-500/15 border-amber-500 ring-1 ring-amber-500 shadow-sm' : 'bg-ink-950 border-ink-border hover:border-amber-500/40' }}">
                        <div class="flex items-center gap-3 min-w-0">
                            <span class="text-2xl shrink-0">{{ $plan['icon'] }}</span>
                            <div class="min-w-0">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="text-xs sm:text-sm font-bold text-paper">{{ $plan['label'] }}</span>
                                    <span class="px-2 py-0.2 rounded-pill text-[10px] font-mono font-bold {{ $plan['min'] === 60 ? 'bg-emerald-500/20 text-emerald-700 dark:text-emerald-300 border border-emerald-500/30' : 'bg-amber-500/15 text-amber-700 dark:text-amber-400' }}">
                                        {{ $plan['badge'] }}
                                    </span>
                                </div>
                                <span class="text-[11px] text-mist block mt-0.5 leading-snug">{{ $plan['desc'] }}</span>
                            </div>
                        </div>
                        <span class="w-5 h-5 rounded-full flex items-center justify-center shrink-0 {{ $daily_minutes_goal === $plan['min'] ? 'bg-amber-500 text-ink-950 font-bold text-xs' : 'border border-ink-border text-transparent' }}">✓</span>
                    </button>
                @endforeach
            </div>

            <!-- Reader Theme Selector -->
            <div class="space-y-2 pt-2 border-t border-ink-border">
                <span class="text-xs font-mono uppercase tracking-wider text-mist block font-semibold">
                    🎨 {{ $t['theme_title'] }}
                </span>
                <div class="grid grid-cols-3 gap-2">
                    @foreach($t['themes'] as $th)
                        <button type="button"
                                wire:click="selectReaderTheme('{{ $th['id'] }}')"
                                class="p-2.5 rounded-xl border text-center transition-all {{ $reader_theme === $th['id'] ? 'bg-amber-500/15 border-amber-500 ring-1 ring-amber-500' : 'bg-ink-950 border-ink-border hover:border-amber-500/40' }}">
                            <span class="text-lg block">{{ $th['icon'] }}</span>
                            <span class="text-xs font-bold text-paper block mt-0.5">{{ $th['label'] }}</span>
                            <span class="text-[10px] text-mist block">{{ $th['desc'] }}</span>
                        </button>
                    @endforeach
                </div>
            </div>

            <!-- Notification Toggles -->
            <div class="p-3.5 rounded-card bg-ink-950 border border-ink-border space-y-2.5">
                <span class="text-[11px] font-mono uppercase tracking-wider text-mist block font-semibold">
                    🔔 {{ $t['notif_title'] }}
                </span>
                <label class="flex items-center justify-between gap-2 cursor-pointer text-xs text-paper">
                    <span>{{ $t['notif_streak'] }}</span>
                    <input type="checkbox" wire:model="notify_streak" class="rounded border-ink-border text-amber-500 focus:ring-amber-500">
                </label>
                <label class="flex items-center justify-between gap-2 cursor-pointer text-xs text-paper">
                    <span>{{ $t['notif_quote'] }}</span>
                    <input type="checkbox" wire:model="notify_daily_quote" class="rounded border-ink-border text-amber-500 focus:ring-amber-500">
                </label>
                <label class="flex items-center justify-between gap-2 cursor-pointer text-xs text-paper">
                    <span>{{ $t['notif_live'] }}</span>
                    <input type="checkbox" wire:model="notify_live" class="rounded border-ink-border text-amber-500 focus:ring-amber-500">
                </label>
            </div>

            <div class="flex items-center justify-between pt-2 border-t border-ink-border">
                <button type="button" wire:click="prevStep" class="ks-btn-ghost py-2 px-4 text-xs font-mono">
                    {{ $t['prev_btn'] }}
                </button>
                <button type="button" wire:click="nextStep" class="ks-btn-primary py-2 px-5 text-xs font-bold">
                    {{ $t['next_btn'] }}
                </button>
            </div>
        </div>
    @endif

    <!-- ══════════════════════════════════════════════════════════════════════
         STEP 5: Profil, Mutolaa Pasporti va Xush Kelibsiz Bonusi
    ═══════════════════════════════════════════════════════════════════════ -->
    @if ($currentStep === 5)
        <div class="space-y-5 animate-fade-in">
            <div class="text-center">
                <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-pill bg-amber-500/15 border border-amber-500/30 text-amber-700 dark:text-amber-300 text-xs font-mono font-bold mb-2">
                    <span>🎁 +50 Ball</span>
                    <span>•</span>
                    <span>🪙 +25 Tanga</span>
                </div>
                <h2 class="text-xl sm:text-2xl font-extrabold font-serif text-paper tracking-tight">{{ $t['s5_title'] }}</h2>
                <p class="text-xs text-mist mt-1">{{ $t['s5_sub'] }}</p>
            </div>

            <!-- Reader Passport Summary Card -->
            <div class="p-4 rounded-2xl bg-ink-950 border border-amber-500/30 space-y-2.5">
                <div class="flex items-center justify-between border-b border-ink-border pb-2">
                    <span class="text-xs font-bold font-serif text-amber-700 dark:text-amber-400">{{ $t['passport_title'] }}</span>
                    <span class="text-[11px] font-mono text-mist">{{ auth()->user()->name ?? 'Kitobxon' }}</span>
                </div>
                <div class="grid grid-cols-2 gap-2 text-xs">
                    <div class="p-2 rounded-btn bg-ink-900 border border-ink-border">
                        <span class="text-[10px] font-mono text-mist block">{{ $t['summary_lang'] }}</span>
                        <span class="font-bold text-paper uppercase">{{ $preferred_locale }} ({{ implode(', ', $reading_languages) }})</span>
                    </div>
                    <div class="p-2 rounded-btn bg-ink-900 border border-ink-border">
                        <span class="text-[10px] font-mono text-mist block">{{ $t['summary_plan'] }}</span>
                        <span class="font-bold text-amber-600 dark:text-amber-400">🔥 {{ $daily_minutes_goal }} {{ $t['min_per_day'] }}</span>
                    </div>
                    <div class="p-2 rounded-btn bg-ink-900 border border-ink-border">
                        <span class="text-[10px] font-mono text-mist block">{{ $t['summary_place'] }}</span>
                        @php
                            $selectedPlaceObj = collect($t['places'])->firstWhere('id', $reading_place ?: 'home');
                        @endphp
                        <span class="font-bold text-paper truncate block">{{ ($selectedPlaceObj['icon'] ?? '🏡') . ' ' . ($selectedPlaceObj['label'] ?? 'Uyda') }}</span>
                    </div>
                    <div class="p-2 rounded-btn bg-ink-900 border border-ink-border">
                        <span class="text-[10px] font-mono text-mist block">{{ $t['summary_goal'] }}</span>
                        @php
                            $selectedGoalObj = collect($t['goals'])->firstWhere('id', $reading_goal ?: 'knowledge');
                        @endphp
                        <span class="font-bold text-paper truncate block">{{ ($selectedGoalObj['icon'] ?? '💡') . ' ' . ($selectedGoalObj['label'] ?? 'Bilim') }}</span>
                    </div>
                </div>
            </div>

            <!-- Avatar Upload & Presets -->
            <div class="space-y-3">
                <label class="block text-xs font-semibold text-mist uppercase tracking-wider">{{ $t['avatar_label'] }}</label>
                <div class="flex items-center gap-3.5">
                    <div class="relative w-16 h-16 rounded-2xl overflow-hidden bg-ink-950 border-2 border-amber-500/40 flex items-center justify-center shrink-0">
                        @if ($avatar)
                            <img src="{{ $avatar->temporaryUrl() }}" class="w-full h-full object-cover">
                        @elseif ($avatar_preset)
                            <span class="text-3xl">{{ $avatar_preset }}</span>
                        @elseif (auth()->user()?->avatar)
                            <img src="{{ auth()->user()->avatar_url }}" class="w-full h-full object-cover">
                        @else
                            <span class="text-2xl">👤</span>
                        @endif
                    </div>
                    <div class="flex-1 min-w-0 space-y-1.5">
                        <input type="file" wire:model="avatar" accept="image/*"
                               class="w-full text-xs text-mist file:mr-2.5 file:py-1.5 file:px-3 file:rounded-btn file:border-0 file:text-xs file:font-bold file:bg-amber-500 file:text-ink-950 hover:file:bg-amber-400 file:cursor-pointer">
                        <span class="text-[10px] text-mist block font-mono">{{ $t['upload_hint'] }}</span>
                        <span wire:loading wire:target="avatar" class="text-[11px] text-amber-600 dark:text-amber-400 block font-mono">{{ $t['uploading'] }}</span>
                        @error('avatar') <p class="text-rose-500 dark:text-rose-400 text-xs">{{ $message }}</p> @enderror
                    </div>
                </div>

                <!-- Avatar Emoji Presets -->
                <div class="flex items-center gap-1.5 flex-wrap pt-1">
                    <span class="text-[11px] text-mist font-mono mr-1">{{ $t['preset_label'] }}</span>
                    @foreach($avatarPresets as $preset)
                        <button type="button"
                                wire:click="selectAvatarPreset('{{ $preset }}')"
                                class="w-8 h-8 rounded-btn border flex items-center justify-center text-base transition-all {{ $avatar_preset === $preset ? 'bg-amber-500/20 border-amber-500 scale-110' : 'bg-ink-950 border-ink-border hover:border-amber-500/40' }}">
                            {{ $preset }}
                        </button>
                    @endforeach
                </div>
            </div>

            <!-- Favorite Book Input -->
            <div>
                <label class="block text-xs font-semibold text-mist uppercase tracking-wider mb-1">{{ $t['fav_book_lbl'] }}</label>
                <input type="text" wire:model.defer="favorite_book" maxlength="120"
                       placeholder="{{ $t['fav_book_ph'] }}"
                       class="w-full px-3.5 py-2 bg-ink-950 border border-ink-border text-paper rounded-xl focus:ring-2 focus:ring-amber-500 focus:outline-none text-xs placeholder-mist">
            </div>

            <!-- Bio Textarea -->
            <div>
                <label class="block text-xs font-semibold text-mist uppercase tracking-wider mb-1">{{ $t['bio_label'] }}</label>
                <textarea wire:model.defer="bio" rows="2" maxlength="300"
                          placeholder="{{ $t['bio_ph'] }}"
                          class="w-full px-3.5 py-2 bg-ink-950 border border-ink-border text-paper rounded-xl focus:ring-2 focus:ring-amber-500 focus:outline-none text-xs placeholder-mist resize-none"></textarea>
                @error('bio') <p class="text-rose-500 dark:text-rose-400 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="flex items-center justify-between gap-3 pt-2 border-t border-ink-border">
                <button type="button" wire:click="prevStep" class="ks-btn-ghost py-2.5 px-4 text-xs font-mono shrink-0">
                    {{ $t['prev_btn'] }}
                </button>
                <button type="button" wire:click="finish"
                        class="ks-btn-primary py-2.5 px-5 font-bold rounded-xl text-xs sm:text-sm shadow-lg transition-all transform active:scale-95 flex items-center justify-center gap-2 flex-1 sm:flex-initial">
                    <span>{{ $t['finish_btn'] }}</span>
                </button>
            </div>
        </div>
    @endif

</div>
