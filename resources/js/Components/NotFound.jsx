import React, { useEffect, useState } from 'react';

/**
 * Single-page, full-viewport Error screen (404, 403, 500, etc.)
 * React + Vite + Tailwind CSS v4
 * Specifically tailored for Kitobxon online book & reading platform.
 * Full tri-lingual (UZ, RU, EN) & Dark/Light mode support.
 */
const DEFAULT_MESSAGES = {
  404: {
    uz: "Bu sahifa kitob javonimizda topilmadi. Keling, sizni mutolaaga qaytaramiz.",
    ru: "Эта страница не найдена на книжной полке. Давайте вернемся к чтению.",
    en: "This page isn't on our bookshelf. Let's get you back to reading.",
  },
  403: {
    uz: "Ushbu sahifani mutolaa qilish huquqingiz cheklangan. Keling, sizni kutubxonaga qaytaramiz.",
    ru: "Доступ к чтению этой страницы ограничен. Давайте вернемся в библиотеку.",
    en: "Reading access to this page is restricted. Let's get you back to the library.",
  },
  500: {
    uz: "Kutubxona tizimida kutilmagan xatolik yuz berdi. Tez orada to‘g‘rilaymiz, mutolaaga qayting.",
    ru: "В библиотечной системе произошла ошибка. Мы уже исправляем, вернемся к чтению.",
    en: "Something went wrong in the library system. We're on it, let's get you back.",
  },
  419: {
    uz: "Mutolaa sessiyasi muddati tugadi. Sahifani yangilang va o‘qishda davom eting.",
    ru: "Время сессии чтения истекло. Обновите страницу и продолжайте чтение.",
    en: "Your reading session has expired. Refresh and continue your journey.",
  },
  429: {
    uz: "Sahifalar juda tez varaqlab yuborildi. Biroz tin oling va mutolaaga qayting.",
    ru: "Страницы листаются слишком быстро. Сделайте паузу и возвращайтесь к чтению.",
    en: "Pages are turning too fast. Take a breather, then let's get back to reading.",
  },
  503: {
    uz: "Kutubxonada yangilanish va ta’mirlash ishlari bormoqda. Tez orada qaytamiz.",
    ru: "В библиотеке проводятся работы по обновлению. Скоро мы вернемся к вам.",
    en: "The library is undergoing scheduled maintenance. We'll be right back.",
  },
};

export default function NotFound({
  code = '404',
  message,
  locale = 'uz', // 'uz' | 'ru' | 'en'
  homeUrl = '/',
  initialTheme = 'auto', // 'dark' | 'light' | 'auto'
}) {
  const [isDark, setIsDark] = useState(true);
  const [currentLocale, setCurrentLocale] = useState(locale);

  const localizedMessage =
    message ||
    DEFAULT_MESSAGES[code]?.[currentLocale] ||
    DEFAULT_MESSAGES[404][currentLocale] ||
    DEFAULT_MESSAGES[404].uz;

  useEffect(() => {
    const checkTheme = () => {
      if (initialTheme === 'dark') {
        setIsDark(true);
        return;
      }
      if (initialTheme === 'light') {
        setIsDark(false);
        return;
      }

      const hasDarkClass = document.documentElement.classList.contains('dark');
      const savedTheme = localStorage.getItem('kitob_theme');
      if (savedTheme === 'light') {
        setIsDark(false);
      } else if (savedTheme === 'dark' || hasDarkClass) {
        setIsDark(true);
      } else {
        const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
        setIsDark(prefersDark);
      }
    };

    checkTheme();

    const handleThemeChange = () => checkTheme();
    window.addEventListener('theme-changed', handleThemeChange);

    const handleKeyDown = (e) => {
      if (!e.ctrlKey && !e.metaKey && !e.altKey) {
        if (e.key === 't' || e.key === 'T') {
          setIsDark((prev) => !prev);
        }
        if (e.key === 'l' || e.key === 'L') {
          const langs = ['uz', 'ru', 'en'];
          setCurrentLocale((prev) => {
            const nextIdx = (langs.indexOf(prev) + 1) % langs.length;
            return langs[nextIdx];
          });
        }
      }
    };
    window.addEventListener('keydown', handleKeyDown);

    return () => {
      window.removeEventListener('theme-changed', handleThemeChange);
      window.removeEventListener('keydown', handleKeyDown);
    };
  }, [initialTheme]);

  return (
    <>
      <style>{`
        @font-face {
          font-family: "Geist Mono:SemiBold";
          font-style: normal;
          font-weight: 600;
          font-display: swap;
          src: url("https://static.figma.com/font/GeistMono_wght__1") format("woff2");
        }

        .geist-mono-semibold {
          font-family: "Geist Mono:SemiBold", ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
          font-weight: 600;
        }

        .error-code-gradient {
          background-image: linear-gradient(
            247.3282658084845deg,
            rgb(255, 255, 255) 2.5334%,
            rgba(255, 255, 255, 0.4) 93.612%
          );
        }

        .error-center-content {
          position: absolute;
          top: 50%;
          left: 50%;
          transform: translate(-50%, -50%);
          width: 483px;
          z-index: 20;
          display: flex;
          flex-direction: column;
          align-items: center;
          text-align: center;
          gap: 44px;
        }

        .error-heading {
          font-size: 295.751px;
          line-height: 1.1;
          letter-spacing: -24.6459px;
          text-align: center;
          padding-bottom: 0.08em;
          height: auto;
          min-height: 0;
          -webkit-background-clip: text;
          background-clip: text;
          color: transparent;
        }

        .error-divider {
          width: 425px;
          height: 1px;
        }

        .error-message {
          font-size: 24px;
          line-height: 1.1;
          letter-spacing: -2px;
          text-align: center;
          width: 100%;
          text-decoration: none;
        }

        @media (max-width: 640px) {
          .error-center-content {
            width: min(calc(100% - 40px), 360px);
            gap: 28px;
          }

          .error-heading {
            font-size: clamp(140px, 52vw, 200px);
            letter-spacing: -0.09em;
            padding-bottom: 0.05em;
          }

          .error-divider {
            width: 100%;
          }

          .error-message {
            font-size: clamp(16px, 4.5vw, 20px);
            letter-spacing: -1.3px;
          }
        }
      `}</style>

      <main
        className="relative min-h-[100svh] w-full overflow-x-hidden overflow-y-auto select-none bg-black text-white"
        style={{ backgroundColor: '#000000', color: '#ffffff' }}
      >
        {/* Semantic Full-Page Background Video */}
        <video
          autoPlay
          loop
          muted
          playsInline
          preload="metadata"
          poster="/media/errors/poster-404.webp"
          aria-hidden="true"
          ref={(el) => {
            if (el && typeof window !== 'undefined') {
              const prefersReduced = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
              const saveData = navigator.connection && (navigator.connection.saveData === true);
              if (prefersReduced || saveData) {
                el.pause();
              }
            }
          }}
          style={{
            position: 'absolute',
            inset: 0,
            width: '100%',
            height: '100%',
            objectFit: 'cover',
            opacity: 1,
            zIndex: 0,
            filter: isDark ? 'brightness(0.82)' : 'brightness(0.95)',
            transition: 'filter 0.4s ease',
            pointerEvents: 'none',
          }}
        >
          <source src="/media/errors/bg-404.webm" type="video/webm" />
          <source src="/media/errors/bg-404.mp4" type="video/mp4" />
          <source src="/media/error-bg.webm" type="video/webm" />
          <source src="/media/error-bg.mp4" type="video/mp4" />
        </video>

        {/* Subtle Black Overlay / Blend Layer */}
        <div
          aria-hidden="true"
          style={{
            position: 'absolute',
            inset: 0,
            width: '100%',
            height: '100%',
            background: isDark
              ? 'radial-gradient(ellipse at center, rgba(0, 0, 0, 0.48) 0%, rgba(0, 0, 0, 0.78) 100%)'
              : 'rgba(0, 0, 0, 0.28)',
            zIndex: 1,
            pointerEvents: 'none',
            transition: 'background 0.4s ease',
          }}
        />

        {/* Centered Error Content */}
        <section className="error-center-content">
          {/* 1. Heading */}
          <h1 className="geist-mono-semibold error-heading error-code-gradient">
            {code}
          </h1>

          {/* 2. Thin Horizontal Divider */}
          <div className="error-divider bg-white" style={{ backgroundColor: '#ffffff' }} />

          {/* 3. Localized Error Message */}
          <a
            href={homeUrl}
            className="geist-mono-semibold error-message text-white"
            style={{ color: '#ffffff' }}
          >
            {localizedMessage}
          </a>
        </section>
      </main>
    </>
  );
}
