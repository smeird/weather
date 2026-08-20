module.exports = {
  content: [
    './frontend/**/*.php',
    './frontend/**/*.js',
    './*.php',
  ],
  darkMode: 'class',
  theme: {
    extend: {},
  },
  plugins: [
    require('@tailwindcss/forms'),
    require('@tailwindcss/typography'),
    require('@tailwindcss/container-queries'),
  ],
};
