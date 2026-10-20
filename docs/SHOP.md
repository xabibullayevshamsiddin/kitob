# Kitobxon — Do'kon (Shop) va Tanga Tizimi Yo'riqnomasi

Ushbu hujjat Kitobxon platformasidagi **Shop** (Tanga do'koni), ballarni tangalarga almashtirish va o'yin iqtisodiyoti tizimining to'liq arxitekturasi hamda undan foydalanish bo'yicha qo'llanma hisoblanadi.

---

## 1. Deploy va O'rnatish Qadamlari

Har qanday yangi server yoki yangilanish (deploy) amalga oshirilganda quyidagi 2 ta buyruq ketma-ket ishga tushirilishi shart:

```bash
# 1. Ma'lumotlar bazasi jadvallarini yangilash
php artisan migrate --force

# 2. Do'kon katalogini o'rnatish va eski foydalanuvchilar xaridlarini ko'chirish
php artisan shop:install
```

### Buyruqlar haqida:

1. **`php artisan shop:install`**:
   - **Xavfsiz va idempotent:** ushbu buyruqni necha marta ishga tushirsangiz ham bazadagi ma'lumotlar buzilmaydi, takrorlanmaydi va adminga tegishli o'zgarishlar o'chib ketmaydi.
   - **Katalog yaratish:** Standart buyumlar (7 ta tema, ramkalar, unvonlar, kuchaytirgichlar) bazada mavjud bo'lmasa yaratiladi (`firstOrCreate`). Agar admin avvalroq biror buyum narxini o'zgartirgan bo'lsa, unga tegilmaydi.
   - **Eski ma'lumotlarni ko'chirish:** Avvalgi `user_profiles.unlocked_items` va jihozlangan temalarni yangi `user_items` va `user_equipment` jadvallariga to'liq o'tkazadi.
   - **Yakuniy hisobot:** Buyruq oxirida katalogdagi buyumlar, foydalanuvchilarning ko'chirilgan inventarlari va jihozlangan buyumlar soni aks etgan qisqa hisobot jadvali chiqariladi.

2. **`php artisan shop:install --refresh-defaults`**:
   - Agar admin o'zgartirgan narxlar yoki tavsiflarni platformaning asl standart qiymatlariga (factory defaults) qaytarish zarur bo'lsa, ushbu bayroq bilan ishga tushiriladi (`updateOrCreate`).

3. **`php artisan shop:migrate-data`**:
   - Faqat ma'lumotlarni ko'chirish logikasini alohida ishga tushirish uchun mo'ljallangan yordamchi buyruq.

---

## 2. Almashtirish Kursi va Chegaralarni Sozlash (Ball → Tanga)

Ballardan tangalarga almashtirish **bir tomonlama** amalga oshiriladi (reyting adolatliligini saqlash uchun tangadan ballga almashtirib bo'lmaydi).

Sozlamalar `config/shop.php` faylida belgilangan va `.env` orqali dinamik boshqariladi:

```env
# 1 tanga olish uchun talab qilinadigan ball (standart: 15 ball = 1 tanga)
SHOP_EXCHANGE_RATE=15

# Bir marta almashtirish uchun minimal ball miqdori
SHOP_EXCHANGE_MIN_POINTS=15

# Foydalanuvchi bir kunda ko'pi bilan almashtirishi mumkin bo'lgan maksimal ball miqdori
SHOP_EXCHANGE_DAILY_LIMIT=1000

# Ball -> Tanga almashtirish funksiyasini yoqish/o'chirish
SHOP_EXCHANGE_ENABLED=true
```

> **Eslatma:** Admin ushbu qiymatlarni shuningdek `/admin/shop?tab=settings` sahifasidan ham bevosita grafik interfeys orqali o'zgartirishi mumkin.

---

## 3. Yangi Buyum Qo'shish va Narxlarni Boshqarish

### Grafik interfeys orqali (Admin Panel):
1. `/admin/shop` manziliga kiring (Admin huquqiga ega foydalanuvchi sifatida).
2. **"Yangi buyum qo‘shish"** tugmasini bosing (`/admin/shop/items/create`).
3. Kategoriya (`bundle`, `profile`, `colors`, `consumable`, `perk`), slot, nodirlik (`common`, `rare`, `epic`, `legendary`), narx va uch tildagi (O'zbek, Rus, Ingliz) nom/tavsiflarni kiriting.
4. Vizual parametrlar (ranglar, CSS stillar yoki effekt kalitlari) avtomatik tekshiruvdan o'tadi va bazaga yoziladi.

### Kod orqali (ShopItemsSeeder):
- Yangi doimiy standart buyumni `database/seeders/ShopItemsSeeder.php` fayliga qo'shishingiz va `php artisan shop:install` buyrug'ini berishingiz mumkin.

---

## 4. Maxsus Imtiyozlar va Ularning Ishlash Tizimi

Do'konda quyidagi maxsus funksional buyumlar mavjud:

| Buyum kaliti (`key`) | Kategoriya | Ta'siri va Ulangan joyi |
| :--- | :--- | :--- |
| `streak_freeze` | `consumable` | **Streak Muzlatgich:** Foydalanuvchi kitob o'qimagan kunida ketma-ketlik buzilmasligi uchun saqlab qoladi (`user_streaks.freeze_count`). Maksimal 5 dona yig'ish mumkin. |
| `coin_booster_1h` | `perk` | **Tanga Kuchaytirgich:** 60 daqiqa davomida o'qilgan har bir daqiqa uchun 2 barobar ko'proq tanga beradi (`PointsService`). Reyting ballariga ta'sir qilmaydi! |
| `quiz_retry_token` | `perk` | **Viktorinani qayta urinish:** Kitob bobi bo'yicha yiqilgan testni qayta topshirish imkoniyati (`TakeQuiz.php`). |
| `ai_questions_pack_50` | `perk` | **AI Yordamchi Savollar Paketi:** Kitob AI assistentida kunlik limit tugaganda sarflanadigan +50 ta qo'shimcha savol limiti (`BookAssistant.php`). |
| `group_slot_expansion` | `perk` | **Qo'shimcha guruh o'rni:** Foydalanuvchi a'zo bo'lishi mumkin bo'lgan kitobxonlik guruhlari sonini +1 taga oshiradi (`GroupList.php`). |
| `live_priority_question` | `perk` | **Jonli efirda ustuvor savol:** Jonli efir vaqtida savolni oltin nishon bilan navbatning eng yuqorisiga chiqaradi (`LiveIndex`, `LiveDetail`, `LiveChatPanel`). |

---

## 5. Xavfsizlik Kafolatlari

1. **Tranzaksiya va Qulflash (`DB::transaction` + `lockForUpdate`):**
   - Foydalanuvchi hisobidan tanga yechish va inventarga yozish paytida foydalanuvchi hisobi bloklanadi (`lockForUpdate`). Ikki marta tez-tez bosish yoki bir vaqtda ikkita oynadan xarid qilish hisobning manfiyga tushib ketishiga yo'l qo'ymaydi.
2. **Ikki marta bosishdan himoya (`purchase_token`):**
   - Har bir xarid oynasi ochilganda mijozga unikal tasodifiy token beriladi. Server bir xil token bilan ikkinchi marta so'rov kelsa, tanga yechmasdan avvalgi xarid natijasini qaytaradi.
3. **Mijoz narxiga ishonmaslik:**
   - Xarid paytida mijoz yuborgan narx emas, bazadagi rasmiy `shop_items.price_coins` olinadi.

---

## 6. Muammolarni Bartaraf Etish (Troubleshooting)

### A. Do'kon sahifasi bo'sh yoki "Do'kon tez orada ochiladi" yozuvi chiqmoqda
- **Sababi:** Ma'lumotlar bazasida `shop_items` jadvali bo'sh.
- **Yechim:** Server konsolida quyidagi buyruqni ishga tushiring:
  ```bash
  php artisan shop:install
  ```

### B. Foydalanuvchining avvalgi sotib olgan temalari ko'rinmayapti
- **Sababi:** `unlocked_items` ustunidan yangi `user_items` jadvaliga ma'lumot ko'chirilmagan.
- **Yechim:** Server konsolida quyidagi buyruqni ishga tushiring:
  ```bash
  php artisan shop:install
  ```

### C. Admin sahifasida ogohlantirish bannerlari chiqmoqda
- `/admin/shop` sahifasi tepasidagi qizil yoki sariq bannerlar katalog to'liq emasligini yoki ma'lumotlar ko'chirilmaganligini bildiradi. Har qanday holatda `php artisan shop:install` buyrug'ini ishga tushirish muammoni to'liq bartaraf etadi.

### D. Keshni tozalash
- Dizayn yoki ko'rinishlar yangilanmagan bo'lsa:
  ```bash
  php artisan view:clear
  php artisan cache:clear
  php artisan config:clear
  ```
