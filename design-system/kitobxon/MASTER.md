# Design System Master File — Kitobxon

> **LOGIC:** When building or redesigning any page or component, first check `design-system/kitobxon/pages/[page-name].md`.
> If that file exists, its rules **override** this Master file.
> If not, strictly follow the rules documented below.

---

**Project:** Kitobxon — Onlayn Kitob O'qish, Ta'lim, Gamifikatsiya va Hamjamiyat Platformasi  
**Stack:** Laravel 9 (Blade) + Livewire 2.12 + Tailwind CSS (Play CDN) + Alpine.js 3.x  
**Category:** Education / Digital Library / Gamified Community  
**Version:** 1.1.0 (Master Single Source of Truth — Fully Finalized)  
**Theme Strategy:** Dual-Theme (Boshlang'ich va standart: **Editorial Dark**, kunduzgi mutolaa hamrohi: **Editorial Light**)  
**The Three Dials:** VARIANCE: 7 (Yuqori o'ziga xoslik) | MOTION: 6 (Silliq fizik harakat) | DENSITY: 4 (Yengil, havodor, mutolaaga mos)

---

## 1. Global Falsafa va Mavzular (Themes)

Kitobxon platformasi "AI-slop" (zerikarli bir xil binafsha gradientlar, sun'iy kartochkalar, 24px yumaloqlikdagi bema'no bloklar)dan butunlay xoli bo'lib, **Editorial Modern + Klassik Kitob estetikasi** tamoyiliga tayanadi.

> **MAVZU QATORI (THEME STRATEGY):**  
> **Standart (default) rejim — Editorial Dark (#07090E Deep Ink)** hisoblanadi. Uning rasmiy kunduzgi hamrohi esa **Editorial Light (#FAF7F2 Krem Vellum qog'oz)** bo'lib, har ikkala mavzu platformada to'laqonli teng huquqli ishlaydi va Tailwind `dark:` klasslari orqali foydalanuvchi xohishiga ko'ra almashadi.

---

## 2. Color Palette (Rang Tizimi)

### 2.1. Editorial Dark (Asosiy / Default Rejim)
| Rol | Token Nomi | Hex Kodi | Tailwind Class / CSS Var | Foydalanish O'rni |
|---|---|---|---|---|
| **Deep Ink Canvas** | `--ink-950` | `#07090E` | `bg-[#07090E]` | Asosiy sahifa orqa foni (Darkest canvas) |
| **Ink Surface** | `--ink-900` | `#0D111A` | `bg-[#0D111A]` | Asosiy kartochkalar, navbar, footer, sidebar |
| **Elevated Ink** | `--ink-800` | `#131926` | `bg-[#131926]` | Hover holatlari, modal oynalar, dropdownlar |
| **Vellum Border** | `--ink-border` | `#1F293D` | `border-[#1F293D]` | Nozik ajratuvchi chiziqlar (1px divider) |
| **Paper Light** | `--paper` | `#F0EDE6` | `text-[#F0EDE6]` | Asosiy matn, sarlavhalar, yorug' kontrast |
| **Muted Slate** | `--text-muted` | `#8B9BAD` | `text-[#8B9BAD]` | Yordamchi matnlar, tavsiflar, sanalar, mualliflar |
| **Gold / Amber** | `--accent-gold` | `#F59E0B` | `text-amber-500` / `bg-amber-500` | Streak, ballar, tangalar, test yutuqlari, nishonlar |
| **Vermilion** | `--accent-book` | `#C1392B` | `bg-[#C1392B]` | Kitob muqovasi urg'usi, asosiy CTA tugmalari foni |
| **Success Emerald** | `--state-success`| `#10B981` | `text-emerald-500` / `bg-emerald-500` | To'g'ri javob, muvaffaqiyatli toast, faol streak |
| **Danger Rose** | `--state-danger` | `#F43F5E` | `bg-rose-500` | Xato, ban bildirishnomasi, jazo foni |
| **Rose-300 (Text)** | `--text-danger` | `#FDA4AF` | `text-rose-300` | Qorong'u fonda WCAG o'tuvchi xato matni (9.8:1) |
| **Info / Indigo** | `--state-info` | `#6366F1` | `text-indigo-400` / `bg-indigo-500` | Yangiliklar, tizim xabarlari, guruh muhokamalari |

### 2.2. Editorial Light (Kunduzgi Hamroh Rejim)
| Rol | Token Nomi | Hex Kodi | Tailwind Class | Foydalanish O'rni |
|---|---|---|---|---|
| **Vellum Canvas** | `--paper-50` | `#FAF7F2` | `bg-[#FAF7F2]` | Kunduzgi mutolaa foni (Krem qog'oz) |
| **Paper Surface** | `--paper-100` | `#FFFFFF` | `bg-white` | Kartochkalar, oq kitob sahifasi |
| **Paper Border** | `--vellum-border`| `#E5DFD5` | `border-[#E5DFD5]` | Qog'oz qirrasi rangidagi nozik chiziqlar |
| **Print Ink Text** | `--ink-900` | `#1A1D24` | `text-[#1A1D24]` | Bosma kitob siyohi kabi quyuq asosiy matn |
| **Slate Annotation**| `--slate-600` | `#526071` | `text-[#526071]` | Yordamchi matnlar, muallif, sana (4.8:1 kontrast) |
| **Deep Vermilion** | `--accent-book-light`| `#B83224` | `bg-[#B83224]` | Yorug' fondagi CTA tugmalari |
| **Warm Gold** | `--accent-gold-light`| `#D97706` | `text-amber-600` | Yorug' fondagi ball va streak ko'rsatkichlari |

### 2.3. Vermilion va Rose uchun Qat'iy Kontrast Qoidasi (WCAG AA 4.5:1)
> ⚠️ **QAT'IY KONTRAST QOIDASI:**  
> - **Deep Ink (`#07090E`) fon ustida Vermilion (`#C1392B`) kontrasti bor-yo'g'i `2.83:1`** ni tashkil qiladi (WCAG 4.5:1 talabiga yetmaydi).  
> - **Deep Ink (`#07090E`) ustida Rose (`#F43F5E`) kontrasti bor-yo'g'i `4.29:1`** ni tashkil qiladi (WCAG 4.5:1 talabiga yetmaydi).  
> 
> **Shu sababli:**
> 1. Vermilion (`#C1392B`) va Rose (`#F43F5E`) **HECH QACHON** qorong'u fon ustida oddiy matn rangi (`text-[#C1392B]`, `text-[#F43F5E]`) sifatida ishlatilmaydi!
> 2. Ular **FAQAT** tugma yoki nishon (badge) orqa foni sifatida ishlatiladi (`bg-[#C1392B]`, `bg-[#F43F5E]`). Ularning ustidagi matn esa doimo Paper Light (`#F0EDE6`, kontrasti `5.71:1` ✅) yoki toza oq bo'ladi.
> 3. Qorong'u fonda qizil/xato tusdagi matn yoki xabar kerak bo'lsa, FAQAT **Rose-300 (`#FDA4AF`, kontrasti `9.8:1` ✅)** yoki **Coral Light (`#FF7A7A`, kontrasti `7.2:1` ✅)** ishlatiladi.

---

## 3. Burchak Radiusi (Border-Radius) Tokenlari

Generic SaaS dasturlaridagi `rounded-3xl` (24px) butunlay chiqarib tashlandi. Editorial bosma kitob va nufuzli jurnallarga xos geometrik intizomli tokenlar:

| Token Nomi | Qiymat | Tailwind Class | Qo'llanish O'rni | Rationale (Asos) |
|---|---|---|---|---|
| `--radius-badge` | `4px` | `rounded` | Teglar, janrlar, format yorliqlari (PDF/Audio/Video) | Ixcham, bosma shtamp hissi |
| `--radius-btn` | `6px` | `rounded-md` | Barcha tugmalar (CTA, Ghost, Secondary), ikon-tugmalar | Qo'l ostida aniq bosiluvchi o'tkir qirra |
| `--radius-input` | `6px` | `rounded-md` | Qidiruv, forma maydonlari, dropdown selectlar | Shakllarda tartib va qat'iy chegaralar |
| `--radius-card` | `8px` | `rounded-lg` | Kitob kartalari (`<x-ui.book-card />`), feed kartalari | Klassik kitob muqovasining qirqim burchagi |
| `--radius-panel` | `12px` | `rounded-xl` | Dashboard panellari, bo'lim bloklari, toast konteyneri | O'rtacha nafis ajralish |
| `--radius-modal` | `16px` | `rounded-2xl` | Modal dialoglar, ban berish modali, alert oynalari | Ekranda suzib chiquvchi yuqori qatlam |

---

## 4. Tipografik Shkala va Qoidalar

Google Fonts ulanishi:
```html
<link href="https://fonts.googleapis.com/css2?family=Spectral:ital,wght@0,400;0,600;0,700;1,400;1,700&family=DM+Sans:wght@300;400;500;600;700&family=DM+Mono:wght@400;500;600&family=JetBrains+Mono:wght@400;500;600&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
```

### 4.1. Aniq Tipografik Jadval

| Daraja | Shrift Oilasi | O'lcham (rem / px) | Line-Height | Weight & Tracking | Foydalanish O'rni |
|---|---|---|---|---|---|
| **Display / H1** | `Spectral`, serif | `2.5rem` (40px) | `leading-[1.3]` (1.3) | 700 (Bold), `-0.02em` | Bosh sahifa hero sarlavhasi, kitob asosiy nomi |
| **H2** | `Spectral`, serif | `2.0rem` (32px) | `leading-[1.35]` (1.35) | 600 (Semibold), `-0.015em`| Bo'lim bosh sarlavhalari, modal bosh sarlavhalari |
| **H3** | `Spectral`, serif | `1.5rem` (24px) | `leading-[1.4]` (1.4) | 600 (Semibold), `-0.01em` | Boblar, guruh nomlari, test sarlavhalari |
| **H4** | `Spectral`, serif | `1.25rem` (20px) | `leading-[1.45]` (1.45) | 600 (Semibold), normal | Karta ichki sarlavhalari, kichik bloklar |
| **Lead / Subtitle**| `DM Sans`, sans-serif| `1.125rem` (18px)| `leading-relaxed` (1.6) | 400 / 500, normal | Sarlavha ostidagi tavsiflar |
| **Body (Matn)** | `DM Sans`, sans-serif| `1.0rem` (16px) | `leading-[1.65]` (1.65)| 400 (Regular), normal | Asosiy kitob matni, maqolalar, chat xabarlari |
| **UI Small** | `DM Sans`, sans-serif| `0.875rem` (14px)| `leading-normal` (1.5) | 500 (Medium), normal | Tugmalar, forma yorliqlari, menyu elementlari |
| **Caption / Meta** | `DM Sans`, sans-serif| `0.75rem` (12px) | `leading-tight` (1.4) | 500 (Medium), normal | Muallif matni, sana, yordamchi izohlar |
| **Stat Large** | `DM Mono`, monospace | `1.75rem` (28px) | `leading-none` (1.0) | 600 (Semibold), tabular | Katta ball, tangalar soni, timer raqami |
| **Badge / Counter**| `DM Mono`, monospace | `0.6875rem` (11px)| `leading-none` (1.0) | 700 (Bold), `+0.05em`, uppercase | Hafta raqami, streak sanog'i, format teglari |

> **Serif Nafas Olish Qoidasi:** Serif (`Spectral`) shriftlar o'zining nozik shtrixlari (serifs va descenderlar) tufayli sans-serifga qaraganda kengroq vertikal bo'shliq talab qiladi. Shu sababli Spectral uchun `1.3 – 1.45` line-height qat'iy belgilandi (sans-serif sarlavhalardagi 1.1–1.2 bu yerda qo'llanilmaydi).

---

## 5. Aniq Animatsiya Tokenlari va Signature Harakatlar

### 5.1. Animatsiya Token Jadvali

| Harakat Turi | Davomiyligi | Easing Funksiyasi (Cubic-bezier) | CSS Token / Xatti-harakat |
|---|---|---|---|
| **Micro Fast** | `150ms` | `cubic-bezier(0.16, 1, 0.3, 1)` | Tugma bosilishi (`active:scale-95`), checkbox, radio |
| **Base Transition** | `200ms` | `cubic-bezier(0.16, 1, 0.3, 1)` | Hover ranglari, chegara yorug'ligi, dropdown ochilishi |
| **Modal / Dialog** | `300ms` | `cubic-bezier(0.16, 1, 0.3, 1)` | Modal ochilish (`scale: 0.95 -> 1`, `opacity: 0 -> 1`) |
| **Page Transition** | `200ms` | `ease-out` | Sahifalar o'rtasidagi yengil fade |

---

### 5.2. Uchta Maxsus "Signature" Harakat

#### 📖 Signature 1: Book 3D Opening (`kitob-ochilish`)
- **Vazifasi:** Kitoblar katalogi va tavsiyalarda sichqoncha karta ustiga borganda kitobning real ochilish fizikasi.
- **Davomiyligi:** `550ms`
- **Easing:** `cubic-bezier(0.22, 1, 0.36, 1)`
- **Fizikasi:**
  - Perspektiva: `1200px`.
  - Muqova rotatsiyasi: `transform-origin: left center; transform: rotateY(-32deg) translateX(4px);`.
  - Qog'oz qirrasi (spine edge): 8px ko'p qatlamli vellum soyasi bilan ochiladi.
  - Orqa muqova: Kitob nomi, janri va qisqa annotatsiyasi bilan ko'rinadi.
  - Ostki soyasi: 12px dan 18px gacha kengayib, kitob stoldan yuqoriga ko'tarilayotgandek optik chuqurlik hosil qiladi (`scaleX(1.1)`).

#### 🔥 Signature 2: Streak Flame Ignition (`streak-olov`)
- **Vazifasi:** Foydalanuvchi kunlik mutolaa me'yorini bajarganda yoki streak uzayganda olovning chaqnashi.
- **Davomiyligi:** `600ms`
- **Easing:** `cubic-bezier(0.34, 1.56, 0.64, 1)` (Elastic rebound)
- **Fizikasi:**
  - `0%`: `transform: scale(1) rotate(0deg);`
  - `30%`: `transform: scale(1.3) rotate(-6deg); filter: drop-shadow(0 0 16px #F59E0B);`
  - `60%`: `transform: scale(1.1) rotate(6deg);`
  - `100%`: `transform: scale(1) rotate(0deg);`

#### 🔔 Signature 3: Toast Notification (`toast-slide-collapse`)
- **Vazifasi:** Platforma bo'ylab barcha xabarlar, ogohlantirishlar va xatoliklarning silliq ko'rinishi va yo'qolishi.
- **Kirish (Entrance):** `350ms`, `cubic-bezier(0.16, 1, 0.3, 1)` — O'ngdan yumshoq surilib kirish (`translateX(100%) -> translateX(0)`) va `opacity: 0 -> 1`.
- **Chiqish va Collapse (Exit & Reflow):** `250ms`, `cubic-bezier(0.4, 0, 1, 1)` — Toast `opacity: 1 -> 0` bo'lib o'ngga suriladi, so'ng `max-height: 120px -> 0` va `margin-bottom: 0` ga qisqaradi. Pastdagi toastlar hech qanday sakrashsiz silliq joyini egallaydi.
- **Taymer Progress Bar:** `4500ms`, `linear` chiziq qisqarishi. Sichqoncha toast ustiga borganda: `animation-play-state: paused` (taymer to'xtaydi).

> **A11y / Reduced Motion:**  
> `@media (prefers-reduced-motion: reduce)` holatida 3D rotatsiyalar, elastic otilishlar va gorizontal surilishlar bekor qilinadi. Faqat `150ms opacity: 0 -> 1` o'tishi qoldiriladi.

---

## 6. Komponentlar Standarti

### 6.1. Tugmalar (Buttons)
```html
<!-- Primary Action (CTA - Vermilion fon + Paper Light matn) -->
<button class="inline-flex items-center justify-center gap-2 px-4 py-2 rounded-md bg-[#C1392B] hover:bg-[#a82e22] text-[#F0EDE6] font-medium text-sm transition-all duration-200 shadow-md hover:-translate-y-0.5 cursor-pointer active:translate-y-0">
  <span>Mutolaani boshlash</span>
</button>

<!-- Secondary Ghost -->
<button class="inline-flex items-center justify-center gap-2 px-4 py-2 rounded-md bg-[#131926] hover:bg-[#1a2336] text-[#F0EDE6] border border-[#1F293D] font-medium text-sm transition-all duration-200 cursor-pointer">
  <span>Batafsil</span>
</button>
```

### 6.2. Shakllar (Inputs & Controls)
- Fon: `bg-[#07090E]`, chegara: `border-[#1F293D]`, burchak: `rounded-md` (6px).
- Fokus: `focus:border-amber-500 focus:ring-1 focus:ring-amber-500`.

---

## 7. Platforma Bo'ylab Yagona Toast-Bildirishnoma Tizimi

Eski 4 ta layoutdagi statik sessiya bannerlari o'rniga butun sayt uchun **yagona x-toast-container** komponenti joylashadi.

- **Desktop Joylashuvi:** Ekranning o'ng-pastki burchagi (`bottom-6 right-6`), vertikal stek.
- **Mobil Joylashuvi (<640px):** Ekran pastida markazlashgan qulay stek (`bottom-4 inset-x-4`).
- **4 ta tur:**
  - `success`: Yashil gradient qirra (`#10B981`), CheckCircle SVG ikonkasi.
  - `error`: Qizil qirra (`#F43F5E`), ExclamationCircle SVG ikonkasi, matni Rose-300 (`#FDA4AF`).
  - `warning`: Amber tillarang (`#F59E0B`), AlertTriangle SVG ikonkasi.
  - `info`: Indigo ko'k (`#6366F1`), InformationCircle SVG ikonkasi.
- **Dual-Bridge:**
  - Livewire: `$this->dispatchBrowserEvent('toast', ['type' => 'success', 'message' => '...'])`
  - Session Flash: Oddiy redirectlardagi `session('success')` avtomatik tarzda JS eventiga o'tadi.

---

## 8. Anti-Patterns (Qat'iyan Man Qilingan Narsalar)

- ❌ **Emoji ikonka sifatida ishlatilmasin** — Faqat Heroicons yoki Lucide SVG ikonkalari.
- ❌ **Generic rounded-3xl kartochkalar** — Har qanday joyda 24px yumaloqlik taqiqlanadi (kartalar 8-12px, tugmalar 6px).
- ❌ **Vermilion va Rose matn rangi sifatida** — Qorong'u fonda kontrast buzilishi sababli faqat fon sifatida ishlatiladi.
- ❌ **Layout-shifting hover** — Hover paytida matnni yoki qatorlarni surib yuboradigan transformatsiyalar taqiqlanadi.
- ❌ **cursor-pointer yetishmasligi** — Barcha bosiladigan tugma, havola, kartalarda majburiy `cursor-pointer`.
- ❌ **Statik bannerlar** — Sahifa o'rtasiga tiqilgan statik `session()` xabarlari taqiqlanadi.

---

## 9. Pre-Delivery Tekshiruv Ro'yxati

Har qanday sahifa yoki komponent yangilanishidan so'ng:
- [ ] Barcha ikonlar SVG formatda va to'g'ri o'lchamda (`w-5 h-5` yoki `w-4 h-4`).
- [ ] Barcha interaktiv elementlarda `cursor-pointer` mavjud.
- [ ] Matn va fon kontrasti kamida 4.5:1 (Vermilion/Rose matn emas, fon bo'lishi tekshirildi).
- [ ] Toast bildirishnomalari Livewire orqali ham, sessiya orqali ham to'g'ri chiqadi.
- [ ] `prefers-reduced-motion` sozlangan foydalanuvchilar uchun animatsiya qulaylashtirilgan.
- [ ] 375px (mobil), 768px (planshet), 1024px (noutbuk) va 1440px (katta ekran) o'lchamlarda to'liq moslashuvchan.
