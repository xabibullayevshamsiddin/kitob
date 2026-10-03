# Catalog Page Overrides — Kitobxon

> **PROJECT:** Kitobxon  
> **Page:** Digital Library Catalog Browsing (`/books`, `/catalog`)  
> **Page Type:** Marketplace / Library Directory / Search & Filter Catalog  
> ⚠️ **IMPORTANT:** Rules in this file **override** the Master file (`design-system/kitobxon/MASTER.md`). Only deviations and catalog-specific guidelines are documented here. For all global tokens, refer to the Master.

---

## 1. Page-Specific Layout & Grid

- **Layout Structure:**
  - Sticky Top Bar: Qidiruv satri (search input debounced 300ms) + Jonli janr teglari (chips carousel) + Saralash (Eng yangi, mashhur, ball bo'yicha).
  - Main Catalog Grid:
    - Kichik mobil (375px–640px): 2 ta ustun (`grid-cols-2`, ixcham muqova).
    - Planshet (768px): 2–3 ta ustun (`md:grid-cols-3`).
    - Noutbuk va Katta Ekran (1024px–1440px): 4 ta ustun (`lg:grid-cols-4`).
- **Karta oralig'i:** `gap-x-5 gap-y-8` (kitobning 3D ochilish harakatiga xalaqit bermaslik uchun kengaytirilgan vertikal bo'shliq).

---

## 2. Component Overrides

### 2.1. 3D Ochiluvchi Kitob Kartasi (`<x-ui.book-card />`)
- **Proportions:** Aspect ratio `2/3` (klassik kitob mutanosibligi).
- **Spine Depth:** Kitobning chap qirrasida 8px qalinlikdagi sahifalar qatlami (multi-layer vellum page-edge).
- **Badges:**
  - Hafta raqami: Yuqori chap burchakda tillarang badge (`#B8860B`).
  - Format indikatorlari: Yuqori o'ngda kichik ixcham yorliqlar (PDF / Audio / Video).
- **Hover Holati:**
  - Muqova 32 gradus chapga aylanadi (`rotateY(-32deg)`).
  - Ostki soyasi 12px dan 18px gacha kengayib, kitob stoldan ko'tarilgandek hajm hosil qiladi.
  - Orqa fonda kitob janri, nomi va qisqacha tavsifi ko'rinadi.

### 2.2. Filtr va Qidiruv Boshqaruvi
- Qidiruv maydonida SVG lupa ikonkasi va kiritilgan matnni tozalash tugmasi (`x`).
- Janrlar filtri bosilganda Livewire orqali sahifa qayta yuklanmasdan kartalar ro'yxati yumshoq yangilanadi.
- Hech narsa topilmaganda (`@empty`): "Natija topilmadi" xabari va "Filtrlarni tozalash" tugmasi.

---

## 3. Anti-Patterns for Catalog
- ❌ **Katalogda haddan tashqari ko'p matnli kartalar** — Asosiy e'tibor kitob muqovasi va nomiga qaratilishi kerak, uzun abzaslar faqat modal yoki ichki sahifada.
- ❌ **Bitta ustunli mobil ko'rinish** — Mobilda kamida 2 ustun bo'lishi shart, aks holda sahifa haddan tashqari uzun cho'zilib ketadi.
- ❌ **Shovqinli harakatlar** — Bir vaqtning o'zida bir nechta kartaning ochilib qolmasligi ta'minlanadi.
