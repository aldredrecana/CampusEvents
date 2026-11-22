import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                // PUP maroon (700 is the main brand color)
                maroon: {
                    50: '#fdf2f2',
                    100: '#fbe3e3',
                    200: '#f5c2c3',
                    300: '#ec9597',
                    400: '#dc5a5e',
                    500: '#c3343a',
                    600: '#a3191f',
                    700: '#800000',
                    800: '#660a0d',
                    900: '#4d080a',
                    950: '#2e0405',
                },
                // PUP gold (400 is the main accent)
                gold: {
                    50: '#fffbea',
                    100: '#fff3c4',
                    200: '#ffe78a',
                    300: '#ffd84d',
                    400: '#fcc419',
                    500: '#f2b705',
                    600: '#d19900',
                    700: '#a67502',
                    800: '#7d5806',
                    900: '#5c4108',
                },
            },
        },
    },

    plugins: [forms],
};
