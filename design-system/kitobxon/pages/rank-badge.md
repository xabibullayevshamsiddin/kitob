# Rank Badge & Top 5 Tier Design Specification — Kitobxon

> **Mavzu:** Peshqadamlar reytingidagi Top 5 foydalanuvchilar uchun markazlashgan vizual maqom va imtiyozlar tizimi.  
> **Kontekst:** Umumiy chat, Reyting jadvali, Profil sahifasi, Guruhlar ro'yxati, Hall of Fame, Avatarlar.

---

## 1. Tier Standartlari va Rang Tokenlari

MASTER.md tokenlariga to'liq asoslangan (arbitrary hex qiymatlarda):

| O'rin (Tier) | Nom / Maqom | Asosiy Rang | Fon (Surface) | Chegara (Border / Ring) | SVG Ikonka |
|---|---|---|---|---|---|
| **1-o'rin** | Oltin (Gold Crown) | `#F59E0B` (Amber Gold) | `bg-[#F59E0B]/15` | `border-[#F59E0B]/50` ring `ring-[#F59E0B]` | 👑 Toj (Crown SVG) |
| **2-o'rin** | Kumush (Silver) | `#E2E8F0` (Paper Silver) | `bg-[#E2E8F0]/10` | `border-[#E2E8F0]/40` ring `ring-[#E2E8F0]` | 🥈 Kumush medal SVG |
| **3-o'rin** | Bronza (Bronze) | `#D97706` (Warm Bronze) | `bg-[#D97706]/15` | `border-[#D97706]/40` ring `ring-[#D97706]` | 🥉 Bronza medal SVG |
| **4 va 5-o'rin** | Top 5 Elita | `#818CF8` (Royal Indigo) | `bg-[#6366F1]/10` | `border-[#6366F1]/30` ring `ring-[#6366F1]/60` | ⭐️ Yulduz / Sparkle SVG |
| **6-o'rin va past** | Oddiy Foydalanuvchi | `#8B9BAD` (Muted Slate) | Standart kartochka | `border-[#1F293D]` (Vellum) | Ikonka yo'q |

---

## 2. Dizayn Qoidalari (Anti-Generic / WCAG)
1. **Emojilar taqiqlangan:** Toj, medal va yulduzlar uchun faqat inline vektor SVG ikonkalardan foydalaniladi.
2. **Arbitrary Hex qiymatlar:** Yangi Tailwind sinflarining keshlanmay qolish xavfini bartaraf etish uchun barcha muhim ranglar to'g'ridan-to'g'ri hex kodlar bilan (`border-[#F59E0B]`, `text-[#F59E0B]`, `bg-[#F59E0B]/10`) ifodalanadi.
3. **Kontrast va O'qilishi:** Chat xabarlari matni doimo Paper Light (`#F0EDE6`) bo'lib qoladi, tier ranglari faqat chegara (border) va kichik sarlavha urg'usi sifatida nozik qo'llanadi.
4. **Harakat:** Nozik mikro-glow va pulslash (faqat `motion-safe` rejimida).
