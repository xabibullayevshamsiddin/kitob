<?php

namespace App\Services\AI\BookAssistant;

use App\Models\Book;
use Illuminate\Support\Facades\Log;

class ConversationRouter
{
    /**
     * Turlar:
     * - general_chat: salomlashish, hol-ahvol, rahmat, xayrlashish, o'zini tanishtirish
     * - humor: do'stona hazil, latifa
     * - complaint: foydalanuvchining noroziligi yoki e'tirozi ("javobing xato", "noto'g'ri")
     * - book_question: kitob mazmuni, qahramonlari, formulalari, betlariga oid savol
     * - mixed: salomlashuv + kitob savoli aralash ("Salom, Otabek kim?")
     * - continuation: oldingi javobni davom ettirish/soddalashtirish ("Tushunmadim, soddaroq ayt")
     * - clarification_needed: juda mujmal yoki noaniq savol
     */
    public const TYPE_GENERAL_CHAT        = 'general_chat';
    public const TYPE_HUMOR               = 'humor';
    public const TYPE_COMPLAINT           = 'complaint';
    public const TYPE_BOOK_QUESTION       = 'book_question';
    public const TYPE_MIXED               = 'mixed';
    public const TYPE_CONTINUATION        = 'continuation';
    public const TYPE_CLARIFICATION       = 'clarification_needed';

    public function __construct(
        protected ProviderManager $providers
    ) {
    }

    /**
     * Savolning muloqot niyatini aniqlaydi.
     *
     * @param  array<int, array{message: string, response: string}>  $historyTurns
     * @return array{
     *     type: string,
     *     sub_type: string|null,
     *     confidence: float,
     *     cleaned_book_question: string|null,
     *     greeting_prefix: string|null,
     *     needs_llm: bool,
     *     reason: string
     * }
     */
    public function route(string $question, ?Book $book = null, array $historyTurns = []): array
    {
        $normalized = $this->normalizeText($question);

        // 1. Prompt Injection yoki tizimni buzish urinishlari -> darhol book_question sifatida qoldirilib, gate da bloklanadi
        if ($this->isPromptInjection($normalized)) {
            return [
                'type'                  => self::TYPE_BOOK_QUESTION,
                'sub_type'              => 'prompt_injection_attempt',
                'confidence'            => 1.0,
                'cleaned_book_question' => $question,
                'greeting_prefix'       => null,
                'needs_llm'             => false,
                'reason'                => 'prompt_injection_detected',
            ];
        }

        // 2. Oldingi javobni davom ettirish yoki soddalashtirish (Continuation)
        // Masalan: "Tushunmadim, soddaroq ayt", "soddaro et", "tushunmadm", "ikkinchisini tushuntir", "davomini ayt"
        if ($this->isContinuationQuery($normalized, $historyTurns)) {
            return [
                'type'                  => self::TYPE_CONTINUATION,
                'sub_type'              => 'simplify_or_expand',
                'confidence'            => 0.95,
                'cleaned_book_question' => $question,
                'greeting_prefix'       => null,
                'needs_llm'             => false,
                'reason'                => 'continuation_intent',
            ];
        }

        // 3. Do'stona hazil so'rash (Humor)
        // Masalan: "Hazil aytib ber", "latifa ayt", "xazil", "kuldir meni"
        if ($this->isHumorRequest($normalized)) {
            return [
                'type'                  => self::TYPE_HUMOR,
                'sub_type'              => 'joke',
                'confidence'            => 0.95,
                'cleaned_book_question' => null,
                'greeting_prefix'       => null,
                'needs_llm'             => false,
                'reason'                => 'humor_intent',
            ];
        }

        // 4. Foydalanuvchi noroziligi / jahldor murojaat (Complaint)
        // MUHIM QOIDA: "Kitobdagi qahramon nega jahli chiqdi?" — bu kitob savoli!
        // Foydalanuvchining shikoyati faqat AI yordamchi javobiga qaratilgan bo'lsagina complaint deb olinadi!
        if ($this->isUserComplaint($normalized, $question)) {
            return [
                'type'                  => self::TYPE_COMPLAINT,
                'sub_type'              => 'dissatisfaction',
                'confidence'            => 0.92,
                'cleaned_book_question' => null,
                'greeting_prefix'       => null,
                'needs_llm'             => false,
                'reason'                => 'user_complaint',
            ];
        }

        // 5. Salomlashuv va kitob savoli aralash murojaat (Mixed)
        // Masalan: "Salom, Otabek kim?", "Assalomu alaykum, 5-betdagi masalani yechib ber", "Qale, bu kitob nima haqida?"
        $mixedCheck = $this->extractMixedGreetingAndQuestion($normalized, $question);
        if ($mixedCheck['is_mixed']) {
            return [
                'type'                  => self::TYPE_MIXED,
                'sub_type'              => 'greeting_plus_book',
                'confidence'            => 0.94,
                'cleaned_book_question' => $mixedCheck['cleaned_question'],
                'greeting_prefix'       => $mixedCheck['greeting_prefix'],
                'needs_llm'             => false,
                'reason'                => 'mixed_greeting_and_question',
            ];
        }

        // 6. Sof oddiy suhbat (General Chat)
        // Masalan: "Salom", "Qalesan", "Rahmat", "Xayr", "Sen kimsan"
        $chatSubType = $this->getPureConversationalSubType($normalized);
        if ($chatSubType !== null) {
            return [
                'type'                  => self::TYPE_GENERAL_CHAT,
                'sub_type'              => $chatSubType,
                'confidence'            => 0.98,
                'cleaned_book_question' => null,
                'greeting_prefix'       => null,
                'needs_llm'             => false,
                'reason'                => 'pure_conversational',
            ];
        }

        // 7. Aniqlashtirish talab qiladigan o'ta qisqa yoki noaniq murojaat
        if ($this->isClarificationNeeded($normalized, $historyTurns)) {
            return [
                'type'                  => self::TYPE_CLARIFICATION,
                'sub_type'              => 'vague',
                'confidence'            => 0.85,
                'cleaned_book_question' => $question,
                'greeting_prefix'       => null,
                'needs_llm'             => false,
                'reason'                => 'vague_query_needs_clarification',
            ];
        }

        // 8. Standart kitobga oid savol (Book Question)
        return [
            'type'                  => self::TYPE_BOOK_QUESTION,
            'sub_type'              => 'content_query',
            'confidence'            => 0.90,
            'cleaned_book_question' => $question,
            'greeting_prefix'       => null,
            'needs_llm'             => false,
            'reason'                => 'standard_book_query',
        ];
    }

    /**
     * Kirillitsa, lotin apostroflari, sleng va qisqartmalarni normallashtiradi.
     */
    public function normalizeText(string $text): string
    {
        $clean = mb_strtolower(trim($text));

        // 1. Emoji va maxsus belgilarni ajratish
        $clean = preg_replace('/[\x{1F600}-\x{1F64F}\x{1F300}-\x{1F5FF}\x{1F680}-\x{1F6FF}\x{2600}-\x{26FF}\x{2700}-\x{27BF}]/u', ' ', $clean) ?? $clean;

        // 2. Apostrof variantlarini yagona standartga keltirish
        $clean = str_replace(
            ['o‘', 'oʼ', 'o`', 'o’', 'g‘', 'gʼ', 'g`', 'g’', 'ʻ', 'ʼ', '’', '`', '‘'],
            ["o'", "o'", "o'", "o'", "g'", "g'", "g'", "g'", "'", "'", "'", "'", "'"],
            $clean
        );

        // 3. Kirillitsa o'zbek/rus harflarini lotin harflariga o'tkazish
        $clean = $this->cyrillicToLatin($clean);

        // 4. Tinish belgilarini bo'shliqqa aylantirish
        $clean = preg_replace('/[?!.,;:)\(_\-\\\/]+/u', ' ', $clean) ?? $clean;
        $clean = preg_replace('/\s+/u', ' ', $clean) ?? $clean;
        $clean = trim($clean);

        // 5. Keng tarqalgan sleng va qisqartmalarni normallashtirish
        $slangMap = [
            'qale'        => 'qalesan',
            'qales'       => 'qalesan',
            'raxmat'      => 'rahmat',
            'katta raxmat'=> 'katta rahmat',
            'xazil'       => 'hazil',
            'tushunmadm'  => 'tushunmadim',
            'tushunmadimku' => 'tushunmadim',
            'soddaro et'  => 'soddaroq ayt',
            'soddaro'     => 'soddaroq',
            'etvor'       => 'aytib ber',
            'aytvor'      => 'aytib ber',
            'yechvor'     => 'yechib ber',
            'echvor'      => 'yechib ber',
            'tushuntirvor'=> 'tushuntirib ber',
            'nma'         => 'nima',
            'bopti'       => 'mayli',
            'obshiy'      => 'umumiy',
        ];

        $words = explode(' ', $clean);
        foreach ($words as $idx => $w) {
            if (isset($slangMap[$w])) {
                $words[$idx] = $slangMap[$w];
            }
        }
        $clean = implode(' ', $words);

        // 2 so'zli frazalar
        foreach (['soddaro et' => 'soddaroq ayt', 'katta raxmat' => 'katta rahmat'] as $from => $to) {
            $clean = str_replace($from, $to, $clean);
        }

        return $clean;
    }

    /**
     * O'zbek va rus kirill harflarini lotin tiliga moslashtirish.
     */
    protected function cyrillicToLatin(string $text): string
    {
        $map = [
            'а' => 'a', 'б' => 'b', 'в' => 'v', 'г' => 'g', 'д' => 'd', 'е' => 'e', 'ё' => 'yo',
            'ж' => 'j', 'з' => 'z', 'и' => 'i', 'й' => 'y', 'к' => 'k', 'л' => 'l', 'м' => 'm',
            'н' => 'n', 'о' => 'o', 'п' => 'p', 'р' => 'r', 'с' => 's', 'т' => 't', 'у' => 'u',
            'ф' => 'f', 'х' => 'x', 'ц' => 'ts', 'ч' => 'ch', 'ш' => 'sh', 'щ' => 'sh', 'ъ' => "'",
            'ы' => 'i', 'ь' => '', 'э' => 'e', 'ю' => 'yu', 'я' => 'ya', 'ў' => "o'", 'қ' => 'q',
            'ғ' => "g'", 'ҳ' => 'h',
        ];

        return strtr($text, $map);
    }

    /**
     * Prompt injection tekshiruvi.
     */
    protected function isPromptInjection(string $text): bool
    {
        $patterns = [
            "oldingi ko'rsatmalarni unut", "oldingi korsatmalarni unut", "barcha qoidalarni unut",
            "ko'rsatmalarni bekor qil", "tizim ko'rsatmasini", "tizim promtini", "system prompt",
            "ignore previous instructions", "ignore all instructions", "disregard previous",
            "forget previous instructions", "reveal system instructions", "jailbreak",
        ];

        foreach ($patterns as $p) {
            if (str_contains($text, $p)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Oldingi javobni davom ettirish yoki soddalashtirish talabi (Continuation).
     */
    protected function isContinuationQuery(string $normalized, array $historyTurns): bool
    {
        $continuationPhrases = [
            'tushunmadim',
            'soddaroq ayt',
            'soddaroq tushuntir',
            'soddaroq qilib tushuntir',
            'oddiyroq tushuntir',
            'oddiyroq ayt',
            'osonroq tushuntir',
            'batafsilroq',
            'batafsil tushuntir',
            'davomini ayt',
            'davom et',
            'ikkinchisini tushuntir',
            'uchinchisini tushuntir',
            'keyingisini ayt',
            'keyingisi chi',
            'yana ayt',
            'yana misol keltir',
            'boshqacha tushuntir',
            'qisqaroq ayt',
            'aniqroq tushuntir',
            'aniqroq ayt',
            'объясни проще',
            'не понял',
            'не поняла',
            'подробнее',
            'продолжай',
            'explain simpler',
            'more details',
            'continue',
        ];

        foreach ($continuationPhrases as $phrase) {
            if ($normalized === $phrase || str_starts_with($normalized, $phrase . ' ') || str_ends_with($normalized, ' ' . $phrase)) {
                return true;
            }
        }

        // "Tushunmadim, soddaroq ayt"
        if (str_contains($normalized, 'tushunmadim') && (str_contains($normalized, 'sodda') || str_contains($normalized, 'oddiy') || str_contains($normalized, 'qaytadan'))) {
            return true;
        }

        return false;
    }

    /**
     * Hazil / latifa so'rovi (Humor).
     */
    protected function isHumorRequest(string $normalized): bool
    {
        $humorKeywords = [
            'hazil ayt', 'hazil aytib ber', 'hazil qil', 'hazillash',
            'latifa ayt', 'latifa aytib ber', 'anekdot ayt', 'anekdot aytib ber',
            'kuldir meni', 'kuldirvor', 'qiziq gap ayt', 'qiziq gap aytib ber',
            'hazil', 'latifa', 'anekdot',
            'пошути', 'расскажи анекдот', 'шутку', 'анекдот',
            'tell a joke', 'tell me a joke', 'make me laugh',
        ];

        $wordCount = count(explode(' ', $normalized));
        if ($wordCount > 7) {
            return false;
        }

        foreach ($humorKeywords as $kw) {
            if ($normalized === $kw || str_starts_with($normalized, $kw . ' ') || str_ends_with($normalized, ' ' . $kw)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Foydalanuvchi noroziligi / jahli (Complaint).
     * DIQQAT: "Kitobdagi qahramon nega jahli chiqdi?" kabi savollar bu yerda rad etiladi!
     */
    protected function isUserComplaint(string $normalized, string $originalText): bool
    {
        // Agar kitobdagi qahramon yoki asar voqealari haqida bo'lsa -> complaint EMAS!
        $bookCharacterIndicators = [
            'qahramon', 'obraz', 'otabek', 'kumush', 'anvar', 'ra\'no', 'asarda',
            'kitobdagi', 'hikoyadagi', 'romandagi', 'asarida', 'nega jahli', 'nega jaxli',
            'nima sababdan jahli', 'xarakteri', 'voqeasi', 'sababi', 'qahramoni',
        ];

        foreach ($bookCharacterIndicators as $bci) {
            if (str_contains($normalized, $bci)) {
                return false;
            }
        }

        $complaintPhrases = [
            "javobing noto'g'ri",
            "javobing notog'ri",
            "javobing xato",
            "noto'g'ri aytding",
            "notog'ri aytding",
            "xato aytding",
            "xato javob berding",
            "noto'g'ri javob",
            "notog'ri javob",
            "bunaqa emas",
            "undaysan",
            "yordam bermayapsan",
            "foydang tegmadi",
            "foydasizsan",
            "asabga tegyapsan",
            "bema'nilik",
            "bemanilik",
            "safsata",
            "sen hech narsani bilmaysan",
            "bilmas ekansan",
            "xato bu",
            "bu xato",
            "ответ неверный",
            "ты ошибся",
            "ты не прав",
            "ты не права",
            "чушь",
            "бесполезный",
            "wrong answer",
            "you are wrong",
            "that is incorrect",
            "incorrect answer",
        ];

        foreach ($complaintPhrases as $cp) {
            if (str_contains($normalized, $cp)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Salomlashuv va kitob savoli aralash murojaatni ajratish (Mixed).
     * Masalan: "Salom, Otabek kim?", "Assalomu alaykum, 5-betdagi masalani yech"
     *
     * @return array{is_mixed: bool, cleaned_question: string|null, greeting_prefix: string|null}
     */
    protected function extractMixedGreetingAndQuestion(string $normalized, string $original): array
    {
        $greetingPatterns = [
            'assalomu alaykum', 'assalom alaykum', 'assalom', 'salomalaykum',
            'salom', 'qalesan', 'qalesiz', 'qalaysiz', 'qalaysan',
            'hayrli kun', 'xayrli kun', 'hayrli tong', 'xayrli tong', 'hayrli kech', 'xayrli kech',
            'privet', 'zdravstvuyte', 'hello', 'hi', 'hey',
        ];

        $matchedGreeting = null;
        $remainingText = null;

        foreach ($greetingPatterns as $gp) {
            if (str_starts_with($normalized, $gp . ' ')) {
                $matchedGreeting = $gp;
                $remainingText = trim(mb_substr($normalized, mb_strlen($gp)));
                break;
            }
        }

        if (!$matchedGreeting || empty($remainingText)) {
            return ['is_mixed' => false, 'cleaned_question' => null, 'greeting_prefix' => null];
        }

        // Qolgan matnda kitobga oid savol bor-yo'qligini tekshiramiz
        $remainingWords = explode(' ', $remainingText);
        if (count($remainingWords) < 2) {
            // Faqat "salom do'stim" yoki "salom aka" kabi murojaat bo'lsa -> mixed EMAS, sof salomlashuv
            return ['is_mixed' => false, 'cleaned_question' => null, 'greeting_prefix' => null];
        }

        // Asl matndan salomlashuv qismini olib tashlab, qolgan savol matnini ajratamiz
        $origClean = preg_replace('/^(salom|assalomu\s+alaykum|assalom\s+alaykum|assalom|qalesan|qalesiz|privet|hello|hi)[,\s!]+/iu', '', trim($original));
        $origClean = trim($origClean);

        return [
            'is_mixed'         => true,
            'cleaned_question' => $origClean !== '' ? $origClean : $remainingText,
            'greeting_prefix'  => $matchedGreeting,
        ];
    }

    /**
     * Sof do'stona suhbat turi (salom, hol-ahvol, minnatdorchilik, xayrlashuv, o'zini tanishtirish).
     */
    protected function getPureConversationalSubType(string $normalized): ?string
    {
        $wordCount = count(explode(' ', $normalized));
        if ($wordCount > 6) {
            return null;
        }

        // Kitobiy atamalar qatnashgan bo'lsa, sof suhbat deb hisoblamaymiz
        $substantiveKeywords = [
            'bob', 'sahifa', 'bet', 'misol', 'mashq', 'yech', 'tarjima', 'formula',
            'muallif', 'tavsif', 'konspekt', 'mazmuni', 'haqida', 'nima degani',
            'qanday qilib', 'chunki', 'tenglama', 'qoida', 'ta\'rif', 'tarif', 'otabek',
            'kumush', 'qahramon', 'obraz', 'mavzu',
        ];

        foreach ($substantiveKeywords as $kw) {
            if (str_contains($normalized, $kw)) {
                return null;
            }
        }

        $categories = [
            'how_are_you' => [
                'nima gaplar', 'nima gap', 'nima xabar', 'nima yangiliklar', 'nima yangilik',
                'qalesiz', 'qalesan', 'qalaysiz', 'qalaysan', 'qandaysiz', 'qandaysan',
                'ishlar qalay', 'ishlar qanaqa', 'ishlar yaxshimi', 'ahvollar qalay', 'ahvollar qanday',
                'tuzukmisiz', 'yaxshimisiz', 'tinchmisiz', 'charchamayapsizmi', 'charchamang', 'charchamanglar',
                'kak dela', 'how are you', 'whats up', 'how is it going',
            ],
            'greeting' => [
                'salom', 'assalomu alaykum', 'assalom alaykum', 'assalom', 'salomalaykum',
                'hayrli kun', 'xayrli kun', 'hayrli tong', 'xayrli tong', 'hayrli kech', 'xayrli kech',
                'privet', 'zdravstvuyte', 'hello', 'hi', 'hey', 'good morning', 'good afternoon', 'good evening',
            ],
            'thanks' => [
                'rahmat', 'katta rahmat', 'tashakkur', 'katta tashakkur', 'minnatdorman',
                'salomat boling', 'sog boling', 'baraka toping', 'spasibo', 'bolshoe spasibo',
                'thank you', 'thanks', 'thank you so much', 'thanks a lot',
            ],
            'farewell' => [
                'xayr', 'xayir', 'hayr', 'hayir', 'mayli xayr', 'mayli hayr', 'korishguncha',
                'poka', 'do svidaniya', 'bye', 'goodbye', 'see you',
            ],
            'identity' => [
                'sen kimsan', 'kimsan', 'siz kimsiz', 'kimsiz', 'kim bu', 'sen kimsan ozi',
                'nima qila olasan', 'nima qila olasiz', 'vazifang nima', 'vazifangiz nima',
                'kto ti', 'kto vi', 'who are you', 'what can you do',
            ],
        ];

        foreach ($categories as $type => $phrases) {
            foreach ($phrases as $phrase) {
                if ($normalized === $phrase || str_starts_with($normalized, $phrase . ' ') || str_ends_with($normalized, ' ' . $phrase)) {
                    return $type;
                }
            }
        }

        return null;
    }

    /**
     * Aniqlashtirish talab qilinadigan noaniq so'rov.
     */
    protected function isClarificationNeeded(string $normalized, array $historyTurns): bool
    {
        $vagueOneWords = ['nima', 'qanday', 'qaysi', 'nega', 'qanaqa', 'kim', 'qayerda'];
        if (in_array($normalized, $vagueOneWords, true) && empty($historyTurns)) {
            return true;
        }

        return false;
    }
}
