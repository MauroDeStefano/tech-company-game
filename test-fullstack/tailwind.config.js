import defaultTheme from 'tailwindcss/defaultTheme'
import colors from 'tailwindcss/colors'
/** @type {import('tailwindcss').Config} */
export default {
  content: [
    "./resources/**/*.blade.php",
    "./resources/**/*.js",
    "./resources/**/*.vue", 
    "./resources/**/*.ts",
    "./resources/js/**/*.{vue,js,ts}",
    "./resources/js/components/**/*.{vue,js,ts}",
    "./resources/js/views/**/*.{vue,js,ts}",
    "./resources/js/layouts/**/*.{vue,js,ts}",
    "./public/**/*.html",
    "./resources/js/app.js",
    "./resources/css/app.css",
  ],
  
  theme: {
    extend: {
      colors: {
        ...colors,
        'tail': {
          50: '#f0fdfa',
          100: '#ccfbf1', 
          200: '#99f6e4',
          300: '#5eead4',
          400: '#2dd4bf',
          500: '#14b8a6',
          600: '#0d9488',
          700: '#0f766e',
          800: '#115e59',
          900: '#134e4a',
          950: '#042f2e',
        },
        
        // Aggiungi i colori success
        'success': {
          50: '#f0fdf4',
          100: '#dcfce7',
          200: '#bbf7d0',
          300: '#86efac',
          400: '#4ade80',
          500: '#22c55e',
          600: '#16a34a',
          700: '#15803d',
          800: '#166534',
          900: '#14532d',
        },
        
        'teal': {
          50: '#f0fdfa',
          100: '#ccfbf1',
          200: '#99f6e4', 
          300: '#5eead4',
          400: '#2dd4bf',
          500: '#14b8a6',
          600: '#0d9488',
          700: '#0f766e',
          800: '#115e59',
          900: '#134e4a',
        }
      },
      // ... resto della configurazione
    },
  },
  
  plugins: [],
}