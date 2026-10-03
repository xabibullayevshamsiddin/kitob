# Admin Analytics Dashboard Overrides — Kitobxon

> **PROJECT:** Kitobxon  
> **Page:** Admin Panel & Analytics Dashboard (`/admin`, `/admin/users`, `/admin/books`, `/admin/stats`)  
> **Page Type:** High-Density Admin Workspace & Analytics Dashboard  
> **Density Override:** **DENSITY: 7** (Asosiy saytdagi DENSITY: 4 o'rniga yuqori axborot zichligi)  
> ⚠️ **IMPORTANT:** Rules in this file **override** the Master file (`design-system/kitobxon/MASTER.md`). Only deviations and admin-specific guidelines are documented here. For all global tokens, refer to the Master.

---

## 1. Page-Specific Rules & Density (DENSITY: 7)

Admin boshqaruvi va tahliliy jadvallar keng bo'shliqlar emas, balki ekranning har bir pikselidan maksimal samarali foydalanishni talab qiladi.

### 1.1. High-Density Spacing va Padding Tokenlari
| Element | Admin Zichligi (DENSITY: 7) | Asosiy Sayt Me'yori (DENSITY: 4) | Tejalgan Joy |
|---|---|---|---|
| **Table Cell Padding** | `py-2.5 px-3.5` | `py-4 px-6` | ~40% ixcham |
| **Grid Oralig'i (Gaps)** | `gap-3` va `gap-4` | `gap-6` va `gap-8` | ~50% ixcham |
| **Stat Card Padding** | `p-4` | `p-6` | ~33% ixcham |
| **Input / Button Height**| `h-9` (36px) | `h-11` (44px) | ~20% ixcham |
| **Modal Padding** | `p-5` | `p-8` | ~35% ixcham |

### 1.2. Admin Tipografik Zichligi
- **Jadval Sarlavhalari (Table Th):** `text-[11px] font-semibold text-slate-400 uppercase tracking-wider`
- **Jadval Ma'lumotlari (Table Td):** `text-xs font-medium text-[#F0EDE6]` (Ismlar, emaillar)
- **Metrika Qiymatlari (KPI):** `text-2xl font-bold font-mono text-[#F0EDE6]`
- **Status va Rol Badgelari:** `text-[10px] font-bold font-mono px-2 py-0.5 rounded`

---

## 2. Color & Role Tokens for Admin

| Rol / Status | Rang Kodi | Badge Class | Qo'llanish O'rni |
|---|---|---|---|
| **Admin** | `#F43F5E` (Rose) | `bg-rose-500/10 text-rose-300 border border-rose-500/20` | Tizim ma'murlari |
| **Ustoz / Teacher** | `#F59E0B` (Amber) | `bg-amber-500/10 text-amber-300 border border-amber-500/20` | O'qituvchilar |
| **Kitobxon / Reader** | `#10B981` (Emerald) | `bg-emerald-500/10 text-emerald-300 border border-emerald-500/20` | Oddiy foydalanuvchilar |
| **Ban / Bloklangan** | `#E11D48` (Crimson) | `bg-red-500/20 text-red-300 font-bold border border-red-500/30` | Ban muddatlari va holati |

---

## 3. Component Specs for Admin

### 3.1. KPI Metrika Kartochkalari (Stat Cards)
- 4 ta asosiy kartochka: Jami foydalanuvchilar, O'quvchilar, Ustozlar, Jami kitoblar.
- Har bir kartada: SVG ikonka (`w-5 h-5`), joriy ko'rsatkich (DM Mono raqamlar bilan), o'tgan haftaga nisbatan dinamika foizi (`+12%`).
- Alpine.js orqali yuklanishda 0 dan real qiymatgacha yumshoq hisoblovchi animatsiya (animated counter).

### 3.2. Data Tables (Jadvallar)
- To'liq ixcham qatorlar (`py-2.5 px-3.5`), qator ustiga borganda `hover:bg-[#131926]`.
- Harakat tugmalari: Tahrirlash (qalamcha), Ban berish (qizil qalqon), O'chirish (axlat qutisi) — barchasi aniq tooltiplar bilan.
- Paginatsiya: Silliq AJAX/Livewire sahifalash.

### 3.3. Ban Boshqaruvi Modali
- Foydalanuvchiga ban berish modali: 1 soat, 1 kun, 1 hafta, 1 oy, butun umrga (permanent).
- Qoidabuzarlik sababini tanlash (Sokinish/Spam/Trolling/Boshqa).
- Qoidabuzarliklar reyestri va tegishli jazo qoidalari havola qilingan holda.

---

## 4. Anti-Patterns for Admin
- ❌ **Katta bo'shliqlar (oversized padding)** — Admin boshqaruvida joyni behuda sarflash va skrollni ko'paytirish taqiqlanadi.
- ❌ **Barcha o'chirishlarni tasdiqlashsiz bajarish** — Har bir xavfli harakat uchun (foydalanuvchini o'chirish, ban) tasdiq modali bo'lishi shart.
- ❌ **Rang orqali bildirishnoma yetishmovchiligi** — Xatolik va muvaffaqiyatlar faqat konsolda emas, yangi Toast tizimi orqali zudlik bilan adminga ko'rinishi shart.
