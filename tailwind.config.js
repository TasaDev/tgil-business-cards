const colors = require('tailwindcss/colors')

module.exports = {
  content: [
    './components/**/*.{vue,js}',
    './layouts/**/*.vue',
    './pages/**/*.vue',
    './plugins/**/*.{js,ts}',
    './nuxt.config.{js,ts}',
  ],
  theme: {
    extend: {
      screens: {
        xs: '360px',
      },
      // Company theme: the stock gray/emerald/black classes are remapped to the
      // TGIL logo navy, so every component picks it up without class changes.
      // Contrast checked: text 100 on 900 15.7:1, helper 400 on 900 8.2:1,
      // white on emerald-600 6.1:1, white on emerald-500 4.7:1.
      colors: {
        black: '#07101f', // input fields
        gray: {
          100: '#eef2f8',
          200: '#d9e1ee',
          400: '#a3b2cc',
          500: '#5a6f95',
          600: '#2a4068',
          700: '#1f3152',
          800: '#14223d',
          900: '#0d1830',
        },
        emerald: {
          500: '#3d6fdb', // hover
          600: '#2c5cc5', // primary actions
        },
      },
      backgroundColor: ['group-focus', 'checked'],
      borderColor: ['checked'],
      brightness: ['hover', 'focus'],
    },
  },
  plugins: [],
}
