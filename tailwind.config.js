/** @type {import('tailwindcss').Config} */
export default {
  darkMode: 'class',
  content: [
    './resources/**/*.blade.php',
    './resources/**/*.js',
    './vendor/livewire/**/*.blade.php',
  ],
  theme: {
    extend: {
      colors: {
        // Editorial Dark — Deep Ink shkalasi (MASTER.md §2.1)
        ink: {
          950: '#07090E',
          900: '#0D111A',
          800: '#131926',
          700: '#1F293D',
          600: '#2A3650',
          border: '#1F293D',
        },
        paper: {
          DEFAULT: '#F0EDE6',
          50: '#FAF7F2',
          100: '#F0EDE6',
          200: '#E5DFD5',
          muted: '#C9C4B8',
        },
        mist: {
          DEFAULT: '#8B9BAD',
          600: '#526071',
        },
        vermilion: {
          DEFAULT: '#C1392B',
          600: '#C1392B',
          700: '#A82E22',
          light: '#B83224',
        },
        gilt: {
          DEFAULT: '#B8860B',
        },
        gold: {
          DEFAULT: '#F59E0B',
          light: '#FBBF24',
        },
        amber: {
          300: '#FCD34D',
          400: '#FBBF24',
          500: '#F59E0B',
          600: '#D97706',
        },
        aurora: {
          teal: '#2DD4BF',
          coral: '#FB7185',
        },
        slate: {
          750: '#1A2236',
          850: '#0D111A',
        },
        // Orqaga moslik (Backward compatibility)
        primary: {
          50: '#eef2ff',
          100: '#e0e7ff',
          200: '#c7d2fe',
          300: '#a5b4fc',
          400: '#818cf8',
          500: '#6366f1',
          600: '#4f46e5',
          700: '#4338ca',
          800: '#3730a3',
          900: '#1e1b4b',
          950: '#07090E',
        },
        accent: {
          300: '#FCD34D',
          400: '#FBBF24',
          500: '#F59E0B',
          600: '#D97706',
        },
        surface: {
          DEFAULT: '#ffffff',
          muted: '#FAF7F2',
          dark: '#0D111A',
          'dark-card': '#131926',
          'dark-border': '#1F293D',
        },
      },
      fontFamily: {
        sans: ['"DM Sans"', 'ui-sans-serif', 'system-ui', 'sans-serif'],
        display: ['Spectral', 'Georgia', 'serif'],
        serif: ['Spectral', 'Georgia', 'serif'],
        mono: ['"DM Mono"', 'ui-monospace', 'monospace'],
        manrope: ['"DM Sans"', 'sans-serif'],
      },
      borderRadius: {
        badge: '4px',
        btn: '6px',
        input: '6px',
        card: '8px',
        panel: '12px',
        modal: '16px',
        xl: '8px',
        '2xl': '10px',
        '3xl': '12px',
      },
      boxShadow: {
        card: '0 4px 20px -2px rgba(0,0,0,0.45)',
        popover: '0 10px 30px -5px rgba(0,0,0,0.6), 0 0 0 1px rgba(255,255,255,0.05)',
        'card-depth': '0 18px 40px -16px rgba(0,0,0,0.7), 0 0 0 1px rgba(255,255,255,0.06)',
        spotlight: '0 0 40px -12px rgba(245,158,11,0.22)',
        'glow-amber': '0 0 20px rgba(245,158,11,0.25)',
        soft: '0 4px 20px -2px rgba(0,0,0,0.45)',
        'soft-lg': '0 10px 40px -10px rgba(0,0,0,0.6)',
      },
      transitionTimingFunction: {
        out: 'cubic-bezier(0.16, 1, 0.3, 1)',
        book: 'cubic-bezier(0.22, 1, 0.36, 1)',
        elastic: 'cubic-bezier(0.34, 1.56, 0.64, 1)',
      },
      transitionDuration: {
        micro: '150ms',
        base: '200ms',
        modal: '300ms',
        book: '550ms',
      },
      animation: {
        'fade-in': 'ksFadeIn 0.3s cubic-bezier(0.16,1,0.3,1) forwards',
        'slide-up': 'ksSlideUp 0.35s cubic-bezier(0.16,1,0.3,1) forwards',
        'slide-in-left': 'ksSlideInLeft 0.3s cubic-bezier(0.16,1,0.3,1) forwards',
        'scale-in': 'ksScaleIn 0.3s cubic-bezier(0.16,1,0.3,1) forwards',
        'streak-flame': 'ksStreakFlame 0.6s cubic-bezier(0.34,1.56,0.64,1)',
        'pulse-slow': 'pulse 3s infinite',
      },
      keyframes: {
        ksFadeIn: { '0%': { opacity: '0' }, '100%': { opacity: '1' } },
        ksSlideUp: { '0%': { opacity: '0', transform: 'translateY(12px)' }, '100%': { opacity: '1', transform: 'translateY(0)' } },
        ksSlideInLeft: { '0%': { opacity: '0', transform: 'translateX(-16px)' }, '100%': { opacity: '1', transform: 'translateX(0)' } },
        ksScaleIn: { '0%': { opacity: '0', transform: 'scale(0.95)' }, '100%': { opacity: '1', transform: 'scale(1)' } },
        ksStreakFlame: {
          '0%': { transform: 'scale(1) rotate(0deg)' },
          '30%': { transform: 'scale(1.3) rotate(-6deg)', filter: 'drop-shadow(0 0 16px #F59E0B)' },
          '60%': { transform: 'scale(1.1) rotate(6deg)' },
          '100%': { transform: 'scale(1) rotate(0deg)' },
        },
      },
    },
  },
  plugins: [
    require('@tailwindcss/forms'),
    require('@tailwindcss/typography'),
  ],
};
