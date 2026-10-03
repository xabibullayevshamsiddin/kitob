# Kitobxon — Redesign Brief (barcha agentlar uchun umumiy)

Loyiha: `d:\OSPanel\domains\localhost\Kitob` — Laravel 9 Blade + Livewire 2.12 + Tailwind Play CDN + Alpine 3. Windows/PowerShell. Python YO'Q.

## Majburiy o'qish (ishni boshlashdan oldin, to'liq)
1. `design-system/kitobxon/MASTER.md` — yagona haqiqat manbai (v1.1, tasdiqlangan).
2. `design-system/kitobxon/pages/catalog.md` va `pages/admin.md` — tegishli bo'lsa ular MASTER'dan ustun.
3. `.agents/skills/taste-skill/SKILL.md` (design-taste-frontend) — anti-slop qoidalari.
4. `resources/views/partials/design-system.blade.php` — TAYYOR tokenlar. Barcha layoutlar uni allaqachon include qiladi. Yangi config yozma, CDN qo'shma, shrift link qo'shma.
5. `resources/views/components/ui/book-card.blade.php` — `<x-ui.book-card :book="$book" :progress="$pct" />` 3D ochiluvchi kitob kartasi (signature). Kitob ro'yxatlarida shuni ishlat.

## Mavjud tokenlar (partialdan)
- Ranglar: `ink-950 #07090E` (fon), `ink-900` (karta/nav), `ink-800` (hover/modal), `ink-700`/`ink-border #1F293D` (chiziq), `paper #F0EDE6` (matn), `mist #8B9BAD` (ikkilamchi matn), `amber-400/500` (ball/streak/aksent), `vermilion #C1392B` (asosiy CTA FONI), `gilt #B8860B` (hafta badge), emerald (success), `rose-300` (qorong'u fonda xato MATNI).
- Shriftlar: `font-sans` = DM Sans, `font-display`/`font-serif` = Spectral, `font-mono` = DM Mono. h1–h4 avtomatik Spectral.
- Radius: `rounded-badge 4px`, `rounded-btn 6px`, `rounded-input 6px`, `rounded-card 8px`, `rounded-panel 12px`, `rounded-modal 16px`. (Eski `rounded-3xl` endi 12px, `rounded-2xl` 10px, `rounded-xl` 8px ga tushirilgan — lekin yangi kodda semantik nomlarni ishlat.)
- Tayyor klasslar: `ks-btn-primary` (vermilion), `ks-btn-gold`, `ks-btn-ghost`, `ks-input`, `ks-card`, `ks-panel`, `ks-badge`, `ks-eyebrow`, `ks-stat`, `ks-rule`, `ks-grain` (qog'oz teksturasi), `ks-flame` + `.is-lit` (streak-olov signature animatsiyasi).
- Easing: `ease-out` (0.16,1,0.3,1), `ease-book`, `ease-elastic`; duration: `duration-micro 150`, `duration-base 200`, `duration-modal 300`.

## Qat'iy qoidalar
1. **Funksionallikni buzma.** Barcha `wire:*`, `x-*`, `@click`, `:class`, `@if/@foreach`, `route()`, `@csrf`, form `name`/`action`/`method`, `id` lar, JS funksiyalar, Livewire property nomlari, `__('site.*')` tarjima kalitlari — SAQLANADI. Faqat markup/klass/vizual tuzilma o'zgaradi. Shubha bo'lsa — saqla.
2. **Emoji ikonka sifatida taqiqlanadi** (📖🎧🔥⭐🏆⚙️👥 va h.k. UI ichida). Inline SVG (Lucide uslubi: `viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"`, `w-4 h-4`/`w-5 h-5`) bilan almashtir. Foydalanuvchi kontentidagi emoji (chat xabarlari, DB matni) — tegma.
3. **AI-slop taqiq:** binafsha/indigo→pushti gradientlar, gradient matnlar, suzuvchi blur "orb"lar, `backdrop-blur` glass hamma joyda, `rounded-3xl` kartochkalar, har kartada bir xil ikonka-kvadrat+sarlavha+matn shabloni, `shadow-2xl` + glow har yerda, "spotlight" sichqoncha nuri. O'rniga: tekis ink sirtlar, 1px `ink-border` chiziqlar, editorial tipografik ierarxiya (Spectral sarlavha + mono eyebrow), asimmetrik grid, nozik `ks-grain`.
4. **Kontrast:** `vermilion` va `rose-500` hech qachon qorong'u fonda matn rangi emas — faqat fon (ustida `text-paper`). Xato matni → `text-rose-300`. Ikkilamchi matn kamida `text-mist` (slate-500/600 qorong'u fonda taqiq).
5. **Interaktivlik:** har bosiladigan elementda hover holati (`transition-colors duration-base`), layout siljitmaydigan hover (scale/translate kartalarda ishlatma, faqat rang/soya/border). Fokus global partialda bor — `focus:outline-none` qo'shib uni o'chirma (agar qo'shsang, `focus-visible:ring-2 focus-visible:ring-amber-500` ber).
6. **Motion:** mikro 150–250ms; sahifada 1–2 ta asosiy animatsiya, cheksiz dekorativ animatsiya yo'q (loaderdan tashqari). GSAP mavjud sahifalarda qolsa bo'ladi, lekin blur/scale kirishlarni yengil `opacity+translateY(12px)` ga tushir.
7. **Responsive:** 375/768/1024/1440 da matn/badge kesilmasin, gorizontal skroll bo'lmasin; mobil nav ishlasin.
8. Faqat SENGA berilgan fayllarni tahrirla. `app/Http/Livewire/Quiz/TakeQuiz.php` va `resources/views/livewire/quiz/take-quiz.blade.php` ga HECH KIM tegmaydi (boshqa ish ketmoqda). PHP/Controller/Model/route/migratsiyaga tegma.
9. git commit/push QILMA — bosh agent qiladi.
10. Ishni tugatgach: `php artisan view:clear` so'ng `php artisan view:cache` ishlat — Blade kompilyatsiya xatosi bo'lmasligi shart (xato bo'lsa tuzat), keyin yana `php artisan view:clear`.
11. Hisobot: tahrirlangan fayllar ro'yxati, har biridagi asosiy dizayn qarori (1 qatordan), saqlab qolingan xavfli joylar, va bajarolmagan narsalar. Qisqa.

## Uslub yo'nalishi (eslatma)
Editorial Dark Modern + klassik kitob. Nashriyot/jurnal hissi: katta Spectral sarlavhalar (H1 2.5rem/1.3, H2 2rem/1.35, H3 1.5rem/1.4), mono eyebrow yorliqlari (`ks-eyebrow`), raqamlar DM Mono tabular, ingichka chiziqlar bilan bo'limlar, keng havo (asosiy sayt DENSITY 4; admin DENSITY 7). VARIANCE 7: har sahifa bir xil 3-ustunli karta panjarasi bo'lmasin — asimmetriya, katta raqamlar, ro'yxat/jadval ko'rinishlari aralash.
