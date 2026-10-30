<?php

namespace App\Services\AI\BookAssistant;

use App\Models\Book;
use App\Models\User;

class ConversationResponder
{
    /**
     * Do'stona muloqot turlariga (salom, hol-ahvol, minnatdorchilik, hazil, e'tiroz)
     * tabiiy, samimiy va kitob hamrohi sifatida mos javob shakllantiradi.
     *
     * MUHIM: Bu javoblar kitob qidiruvi/vektorlarini chaqirmaydi va sun'iy "1-bet" manbasini biriktirmaydi (sources: []).
     */
    public function respond(
        string $type,
        ?string $subType,
        Book $book,
        ?User $user = null,
        array $extra = []
    ): string {
        $userName = $user ? trim((string) ($user->name ?? '')) : '';
        $bookTitle = trim((string) $book->title);
        $isIndexing = in_array($book->ai_index_status, ['indexing', 'none'], true);

        // 1. Agar kitob hali indekslanayotgan bo'lsa
        if ($isIndexing) {
            if ($type === ConversationRouter::TYPE_GENERAL_CHAT && $subType === 'greeting') {
                return $userName !== ''
                    ? "Assalomu alaykum, {$userName}! «{$bookTitle}» kitobining sahifalarini ayni paytda indekslayapman. Tez orada kitobning barcha sahifalari bo‘yicha to‘liq yordam bera olaman. Ungacha savollaringiz bo‘lsa, bemalol so‘rashingiz mumkin!"
                    : "Assalomu alaykum! «{$bookTitle}» kitobining sahifalarini ayni paytda indekslayapman. Birozdan so‘ng kitob bo‘yicha barcha savollaringizga to‘liq javob bera olaman!";
            }

            if ($type === ConversationRouter::TYPE_HUMOR) {
                return "Kitob hali indekslanmoqda, lekin bitta adabiy hazil: Nega kitoblar hech qachon sovuq qotmaydi? Chunki ularning qalin muqovasi (varaqlari) bor! 😊 «{$bookTitle}» kitobi tayyor bo‘lgach, yana qiziqarli narsalarni o‘rganamiz!";
            }
        }

        // 2. Hazil so'rovi (Humor)
        if ($type === ConversationRouter::TYPE_HUMOR) {
            $jokes = [
                "Kutubxonachi o‘quvchiga debdi:\n— Bu kitob sizning barcha muammolaringizning yarmini hal qiladi!\nO‘quvchi o‘ylanib turib:\n— Unda menga ikkita bering, barcha muammolarim tugasin! 😄",
                "O‘quvchi kitob do‘koniga kirib so‘rabdi:\n— Kechirasiz, «Bir kunda qanday qilib dangasalikdan qutulish mumkin?» degan kitob bormi?\nSotuvchi:\n— Bor, ammo uni javondan tushirishga erinyapman... 📚😅",
                "Matematika kitobi nega xafa bo‘lib qolibdi?\nChunki uning juda ko‘p muammolari (masalalari) bor ekan! 📐😄",
                "Bir kitobxon do‘stiga debdi:\n— Men kitob o‘qishni shunchalik yaxshi ko‘ramanki, hatto telefonim zaryadi 100% bo‘lganda ham kitob o‘qiyman! 📖✨",
            ];
            return $jokes[array_rand($jokes)];
        }

        // 3. Foydalanuvchi noroziligi yoki e'tirozi (Complaint)
        if ($type === ConversationRouter::TYPE_COMPLAINT) {
            return "Kechirasiz, noaniqlik yoki tushunmovchilik uchun uzr so‘rayman. Men «{$bookTitle}» kitobi matniga qat'iy tayangan holda xizmat qilaman. Qaysi savol yoki sahifada xatolik yuz berganini aniqroq aytsangiz, dalillarni qaytadan sinchkovlik bilan tekshirib, to‘g‘ri ma'lumot berishga tayyorman.";
        }

        // 4. Aniqlashtirish so'rovi (Clarification needed)
        if ($type === ConversationRouter::TYPE_CLARIFICATION) {
            return "Savolingizni biroz aniqlashtirib bera olasizmi? «{$bookTitle}» kitobining aynan qaysi bobi, sahifasi, qahramoni yoki masalasi haqida bilmoqchisiz?";
        }

        // 5. Oddiy suhbat (General Chat) turlari
        if ($type === ConversationRouter::TYPE_GENERAL_CHAT) {
            switch ($subType) {
                case 'greeting':
                    $greetings = [
                        $userName !== ''
                            ? "Assalomu alaykum, {$userName}! «{$bookTitle}» kitobi bo‘yicha qanday savolingiz bor? Sizga yordam berishdan xursandman."
                            : "Assalomu alaykum! «{$bookTitle}» kitobi bo‘yicha qanday savollaringiz bor? Marhamat, so‘rashingiz mumkin.",
                        $userName !== ''
                            ? "Salom, {$userName}! «{$bookTitle}» kitobini mutolaa qilayotganingizdan xursandman. Qaysi sahifa yoki mavzuni tahlil qilamiz?"
                            : "Salom! «{$bookTitle}» kitobining repetitor yordamchisiman. Sizga qanday yordam bera olaman?",
                    ];
                    return $greetings[array_rand($greetings)];

                case 'how_are_you':
                    return "Rahmat, ajoyib! «{$bookTitle}» kitobining barcha sahifalari va boblarini diqqat bilan o‘rganib, sizga yordam berishga doim tayyorman. Sizda mutolaa qanday ketyapti?";

                case 'thanks':
                    $thanksReplies = [
                        "Arzimaydi! Mutolaangiz yanada maroqli va unumli bo‘lsin. Yana savollaringiz bo‘lsa, bemalol bering!",
                        "Salomat bo‘ling! «{$bookTitle}» kitobi bo‘yicha yana biror mavzu qiziqtirsa, doim xizmatingizdaman.",
                        "Foydam tekkanidan judayam xursandman! Yaxshi mutolaa tilayman. ✨",
                    ];
                    return $thanksReplies[array_rand($thanksReplies)];

                case 'farewell':
                    $farewellReplies = [
                        "Xayr, salomat bo‘ling! Kitob mutolaangiz bardavom bo‘lsin. Keyingi suhbatlargacha!",
                        "Ko‘rishguncha! «{$bookTitle}» kitobini o‘qishda sizga ulkan muvaffaqiyatlar tilayman.",
                    ];
                    return $farewellReplies[array_rand($farewellReplies)];

                case 'identity':
                    return "Men «{$bookTitle}» kitobining rasmiy, bilimdon va sabrli repetitor AI yordamchisiman. Vazifam — ushbu kitob matniga, faktlariga va formulalariga tayangan holda savollaringizga aniq, tushunarli va ishonchli javob berishdir.";

                default:
                    return "Salom! «{$bookTitle}» kitobi bo‘yicha sizga qanday yordam bera olaman?";
            }
        }

        return "Salom! «{$bookTitle}» kitobi bo‘yicha qanday savollaringiz bor?";
    }
}
