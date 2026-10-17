# Kitobxon — Admin Analitika Metrika Ta'riflari va Arxitekturasi (`docs/ANALYTICS.md`)

Ushbu hujjat platformaning Admin Analitika bo'limidagi barcha ko'rsatkichlar (KPI), ularning aniq matematik va SQL ta'riflari, vaqt zonasi qoidalari hamda ma'lumotlar manbalarini belgilaydi.

---

## 1. Umumiy Qoidalar va Vaqt Zonasi

| Parametr | Qiymat / Qoida |
|---|---|
| **Vaqt zonasi** | `Asia/Tashkent` (`UTC+5`, `config('analytics.timezone')` va `config('app.timezone')`) |
| **Kun chegarasi** | Har bir kun `00:00:00` dan `23:59:59` (`Asia/Tashkent`) gacha hisoblanadi |
| **Snapshot vaqti** | Har kecha soat `00:10` (`Asia/Tashkent`) da `php artisan analytics:snapshot` orqali `daily_metrics` jadvaliga yoziladi |
| **Tarixiy va Jonli ma'lumot** | O'tgan sanalar `daily_metrics` jadvalidan o'qiladi; **bugungi kun** (`today`) esa jonli (live) so'rovdan hisoblanadi |
| **Keshlash** | Jonli qismlar `Cache::remember` orqali **5 daqiqa** (`300s`) keshlanadi. Kalit tarkibida davr mavjud (`analytics:{metric}:{preset}:{from}:{to}`). Admin `?refresh=1` parametri bilan keshni majburan yangilashi mumkin |

---

## 2. Metrika Ta'riflari Jadvali

| # | Metrika | Aniq ta'rif va Formula | Manba jadval(lar) | Maxsus qoidalar / Cheklovlar |
|---|---|---|---|---|
| 1 | **Faol foydalanuvchi (kunlik)** | `daily_activities` jadvalida o'sha sanaga (`activity_date`) yozuvi bor **VA** (`logged_in = 1` **YOKI** `minutes_read > 0`) bo'lgan noyob `user_id`lar soni. | `daily_activities` | **"Kirgan"** (`logged_in = 1`) va **"O'qigan"** (`minutes_read > 0`) alohida ham ko'rsatiladi. *(Kod auditi eslatmasi: hozirda `DailyActivity::getTodayActivity()` faqat o'qish heartbeat'i va bonus olish paytida chaqiriladi).* |
| 2 | **DAU** | Tanlangan davrning oxirgi kunidagi (yoki bugungi) noyob faol foydalanuvchilar soni. | `daily_activities`, `daily_metrics` | Oldingi kunga nisbatan delta hisoblanadi. |
| 3 | **WAU** | Oxirgi **7 kun** (`[end - 6 kun, end]`) ichida kamida 1 marta faol bo'lgan noyob foydalanuvchilar soni. | `daily_activities` | Oldingi 7 kunlik (`[end - 13 kun, end - 7 kun]`) davrga nisbatan delta hisoblanadi. |
| 4 | **MAU** | Oxirgi **30 kun** (`[end - 29 kun, end]`) ichida kamida 1 marta faol bo'lgan noyob foydalanuvchilar soni. | `daily_activities` | Oldingi 30 kunlik (`[end - 59 kun, end - 30 kun]`) davrga nisbatan delta hisoblanadi. |
| 5 | **Yopishqoqlik (Stickiness)** | $\text{Stickiness} = \text{round}\left(\frac{\text{DAU}}{\text{MAU}} \times 100,\ 1\right)\%$ | `daily_activities` | Agar $\text{MAU} = 0$ bo'lsa, `0.0%` qaytariladi. |
| 6 | **Faol o'quvchi (Active Reader)** | Kuniga kamida **5 daqiqa** (`minutes_read >= 5`) kitob o'qigan noyob foydalanuvchi. | `daily_activities` | Me'yor `config('analytics.active_reader_min_minutes', 5)` orqali boshqariladi. |
| 7 | **Tugatilgan kitob (`books_finished`)** | `book_reading_progress.percent_complete >= 90` shartini bajargan **noyob `(user_id, book_id)` juftliklari** soni. | `book_reading_progress` | `chapter_id` tufayli bitta kitobda bir nechta bob qatori bo'lsa ham, `GROUP BY user_id, book_id` orqali bitta foydalanuvchi uchun bitta kitob **faqat 1 marta** sanaladi. |
| 8 | **Retention D1 / D7 / D30** | `users.created_at` (`Asia/Tashkent` sanasi $d$) bo'yicha ro'yxatdan o'tgan kohortaning $d + n$ ($n \in \{1, 7, 30\}$) sanada faol bo'lgan ulushi (%). | `users`, `daily_activities` | Agar $d + n > \text{bugun}$ bo'lsa (hali $n$ kun o'tmagan kohorta), qiymat **`null` (`N/A`)** qaytariladi — hech qachon `0` emas! |
| 9 | **Voronka (Funnel)** | 1. **Ro'yxatdan o'tdi** (`users.created_at` davrda)<br>2. **Onboarding tugadi** (`user_profiles.reading_place IS NOT NULL AND != ''`, `EnsureOnboardingIsComplete` bilan bir xil)<br>3. **Birinchi o'qish** (`minutes_read > 0`)<br>4. **2-kuni qaytdi** ($d + 1$ sanada faol)<br>5. **7 kunlik streak** (`longest_streak >= 7` yoki `current_streak >= 7`) | `users`, `user_profiles`, `daily_activities`, `reading_sessions`, `user_streaks` | Har bosqich uchun jami ro'yxatdan o'tganlarga nisbatan % va oldingi bosqichga nisbatan konversiya % hisoblanadi. |
| 10 | **Quiz o'rtacha foizi va savol tahlili** | Faqat yakunlangan (`completed_at IS NOT NULL`) va baholangan (`review_status IS NULL OR review_status != 'pending'`) urinishlarning `AVG(percent)` qiymati. | `quiz_attempts`, `quizzes` | `quiz_attempts.answers` JSON ichida `question_id` va `correct` (`bool`) saqlangani uchun **ham quiz darajasida, ham savol darajasida** to'g'ri javob foizi hisoblanadi. |
| 11 | **Delta (o'zgarish ko'rsatkichi)** | Tanlangan davr ($N$ kun) qiymatini bevosita oldingi teng davr ($N$ kun) bilan solishtirish: $\frac{\text{current} - \text{previous}}{\text{previous}} \times 100$. | Barcha davriy jadvallar | Agar `previous == 0` va `current > 0` bo'lsa: `percent = null`, `label = "yangi"` (cheksiz foiz emas!). Shikoyat va xato kabi "yomon o'sadigan" metrikalarda ranglar teskari (`invertColors = true`). |
| 12 | **Segmentlar** | `user_profiles.reading_goal` va `reading_place` bo'yicha guruhlash. | `user_profiles` | Guruhda **5 tadan kam** (`< 5`) foydalanuvchi bo'lsa, u guruh alohida ko'rsatilmaydi va `"kam ma'lumot"` yig'indisiga birlashtiriladi. |
| 13 | **Yo'qolish xavfi (Churn Risk)** | `current_streak >= 7` (yoki `longest_streak >= 7`) bo'lib, oxirgi **2+ kun** ichida faol bo'lmagan foydalanuvchilar. | `user_streaks`, `daily_activities`, `users` | Maxfiylik: faqat `username` va admin tahrirlash sahifasiga havola (`email` va to'liq `name` chiqarilmaydi). |
| 14 | **Suiiste'mol belgilari (Abuse Signals)** | Kunlik yig'ilgan musbat ballar (`point_transactions.points > 0`) bo'yicha foydalanuvchi-kun kesimida $z = \frac{x - \mu}{\sigma} \ge 3.0$ bo'lgan anomaliyalar. | `point_transactions`, `users` | Faqat `"tekshirilsin"` maqomidagi ro'yxat (`username` + admin havolasi). Avtomatik jazo yoki ban **QO'LLANMAYDI**. |
| 15 | **AI xarajati va token byudjeti** | $\text{Cost (USD)} = \frac{\sum \text{tokens\_used}}{1000} \times \text{config('analytics.ai\_cost\_per\_1k')}$ | `ai_chat_histories` | Kunlik token sarfi `config('analytics.ai_daily_token_budget')` dan oshsa, ogohlantirish chiqadi. |

---

## 3. Kod Auditi Topilmalari (1-bosqich tekshiruvi)

1. **`books_finished` ("Kitob tugatildi") tafovuti:**
   - `book_reading_progress` jadvalida `['user_id', 'book_id', 'chapter_id']` bo'yicha qatorlar saqlanadi (har bir bob uchun alohida qator bo'lishi mumkin).
   - `app/Http/Livewire/Profile/ProfilePage.php` (306-qator) da `BookReadingProgress::where('user_id', $this->user->id)->where('percent_complete', '>=', 90)->count()` yozilgan — bu kitoblarni emas, **qatorlarni (boblarni)** sanaydi (masalan, 1 ta kitobning 5 ta bobini 90%+ o'qigan foydalanuvchida `books_finished = 5` chiqadi).
   - `app/Http/Livewire/Catalog/CatalogPage.php` (99-qator) da esa `Book::whereHas('readingProgress', ... percent_complete >= 90)->count()` ishlatilgan — u kamida bitta bobi `>= 90%` bo'lgan kitoblarni noyob kitob sifatida sanaydi.
   - `app/Models/BookReadingProgress.php` (`getIsFinishedAttribute`, 54-qator) da esa `percent_complete >= 100.0` tekshirilgan.
   - **Analitika yechimi:** Mavjud `ProfilePage` va `CatalogPage` mantiqiga jim tegilmadi; analitika servislarida (`OverviewMetrics::countDistinctFinishedBookPairs` va `ContentMetrics`) `percent_complete >= 90` bo'yicha **noyob `(user_id, book_id)` juftliklari** (`GROUP BY user_id, book_id`) sanaladi.
2. **`daily_activities.logged_in` yozilishi:**
   - `DailyActivity::getTodayActivity()` metodi yangi qator yaratganda `logged_in = true` qilib yozadi, biroq bu metod hozirgi kod bazasida faqat `/api/reading/heartbeat`, `/api/reading/claim-hourly-bonus` va `NotificationList` ichida chaqiriladi. Oddiy login yoki sahifa ochishda (`TrackDailyStreak`) o'qish bo'lmasa `daily_activities` qatori yaratilmaydi.
3. **Ma'lumotlar bazasi indekslari:**
   - `daily_activities(activity_date)` indeksi `2024_01_01_000020_create_daily_activity_table.php` migratsiyasida allaqachon mavjud (`daily_activities_activity_date_index`).
   - `users(created_at)` va `reading_sessions(book_id, session_date)` / `reading_sessions(session_date, book_id)` indekslari mavjud emas edi — ular `2026_10_17_100001_add_analytics_performance_indexes.php` migratsiyasida qo'shildi.
