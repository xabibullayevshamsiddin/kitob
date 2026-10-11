# 🎬 Error Page Background Video Processor (Multi-Error Stone Text Engraving)

Ushbu vosita xato sahifalarining (`404`, `403`, `419`, `429`, `500`, `503`) fon videosidagi tosh ustidagi begona yozuvni saytimizga moslashtirilgan mualliflik ibratli so'zlar bilan tosh yuziga o'yilgan holatda har bir xatolik uchun alohida generatsiya qiladi.

---

## 📜 Xatoliklar Bo'yicha Toshga O'yilgan Hikmatlar

| Xatolik | Mavzusi | Toshga O'yilgan 3 Qator | Mazmuni |
|:---|:---|:---|:---|
| **404** | Adashgan / Topilmagan sahifa | `ADASHMOQ HAM` / `YO‘LNING BOSHI` / `QIDIRGAN TOPAR` | Izlash ham yo'lning bir qismi |
| **403** | Ruxsat cheklangan hudud | `SABR QIL, HAR ESHIK` / `O‘Z VAQTIDA` / `OCHILAJAKDIR` | Sabr va ilm eshigi odobi |
| **419** | Sessiya muddati tugadi | `G‘ANIMATDUR BU DAM` / `FURSATNI BOYLAMA` / `MUTOLAAGA QAYT` | Vaqt va umr qadri |
| **429** | Shoshqaloqlik / Ko'p so'rovlar | `SHOSHGANNING SHOSHI` / `DONONING BARDOSHI` / `SOKIN VARAQLA` | Shoshmaslik fazilati |
| **500** | Kutilmagan uzilish / Xatolik | `HAR BIR TO‘SIQDA` / `BIR HIKMAT BOR` / `SABR ET, INSON` | Falsafiy sabr va xotirjamlik |
| **503** | Ta'mirlash / Profilaktika | `TIN OLGAN AQL` / `KUCHGA TO‘LIB` / `QAYTA BOSHLAYDI` | Yangilanish va kuch to'plash |

---

## ✍️ So'zlarni Tahrirlash

[`tools/error-video/process_video.py`](./process_video.py) faylining yuqorisidagi `ERROR_QUOTES` lug'atini ochib, istalgan kod uchun matnni o'zgartirishingiz mumkin:

```python
ERROR_QUOTES = {
    "404": [
        "ADASHMOQ HAM",
        "YO‘LNING BOSHI",
        "QIDIRGAN TOPAR"
    ],
    # ...
}
```

---

## 🚀 Skriptni Ishga Tushirish

Loyihaning asosiy jildida terminaldan ishga tushiring:

```bash
uv run --python 3.12 --with opencv-python --with pillow --with numpy python tools/error-video/process_video.py
```

---

## 📂 Yaratiladigan Chiqish Fayllari

Skript avtomatik tarzda `public/media/errors/` jildiga har bir xatolik uchun quyidagi fayllarni joylaydi:

- `public/media/errors/bg-404.mp4` / `bg-404.webm` / `poster-404.webp`
- `public/media/errors/bg-403.mp4` / `bg-403.webm` / `poster-403.webp`
- `public/media/errors/bg-419.mp4` / `bg-419.webm` / `poster-419.webp`
- `public/media/errors/bg-429.mp4` / `bg-429.webm` / `poster-429.webp`
- `public/media/errors/bg-500.mp4` / `bg-500.webm` / `poster-500.webp`
- `public/media/errors/bg-503.mp4` / `bg-503.webm` / `poster-503.webp`
- Zaxira (fallback) uchun: `public/media/error-bg.mp4`, `error-bg.webm`, `error-bg-poster.webp`
