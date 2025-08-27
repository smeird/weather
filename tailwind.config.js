const colors = require('tailwindcss/colors');

module.exports = {
  darkMode: 'class',
  content: [
    './**/*.{php,js,html}',
    '!./node_modules/**/*',
    '!./vendor/**/*',
  ],
  theme: {
    extend: {
      colors: {
        brand: colors.sky,
        surface: {
          bg: {
            DEFAULT: '#f8fafc',
            dark: '#0b1220',
          },
          panel: {
            DEFAULT: '#ffffff',
            dark: '#0f172a',
          },
          elevated: {
            DEFAULT: '#f1f5f9',
            dark: '#111827',
          },
        },
        muted: {
          border: {
            DEFAULT: '#e2e8f0',
            dark: '#1f2937',
          },
        },
        state: {
          success: '#10b981',
          error: '#f43f5e',
          warning: '#f59e0b',
          info: '#3b82f6',
        },
      },
      fontFamily: {
        sans: ['Inter', 'ui-sans-serif', 'system-ui', 'Segoe UI', 'Roboto', 'Helvetica Neue', 'Arial'],
        mono: ['ui-monospace', 'SFMono-Regular', 'Menlo', 'Consolas', 'Monaco'],
      },
      boxShadow: {
        'elev-1': '0 1px 2px 0 rgb(0 0 0 / 0.06)',
        'elev-2': '0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1)',
      },
      borderRadius: {
        xl: '0.75rem',
        '2xl': '1rem',
      },
      transitionDuration: {
        150: '150ms',
        200: '200ms',
        300: '300ms',
      },
      keyframes: {
        'fade-in': { '0%': { opacity: 0 }, '100%': { opacity: 1 } },
        'slide-in-left': { '0%': { transform: 'translateX(-8px)', opacity: 0 }, '100%': { transform: 'translateX(0)', opacity: 1 } },
      },
      animation: {
        'fade-in': 'fade-in 300ms ease-out',
        'slide-in-left': 'slide-in-left 200ms ease-out',
      },
    },
  },
  plugins: [
    require('@tailwindcss/forms'),
    require('@tailwindcss/typography'),
    require('@tailwindcss/container-queries'),
  ],
};
