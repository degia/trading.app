import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    darkMode: 'class',

    theme: {
        extend: {
            colors: {
                ink: {
                    DEFAULT: '#0a0a0c',
                    2: '#141418',
                    3: '#1c1c22',
                },
                paper: {
                    DEFAULT: '#f7f7f5',
                    2: '#ffffff',
                    3: '#ececea',
                },
                profit: {
                    DEFAULT: '#20e3a2',
                    dim: '#0f7a58',
                },
                loss: {
                    DEFAULT: '#ff5470',
                    dim: '#8f2338',
                },
                target: {
                    DEFAULT: '#e8c468',
                },
                mute: {
                    d: '#8b8b93',
                    l: '#6b6b70',
                },
            },
            fontFamily: {
                display: ['Space Grotesk', ...defaultTheme.fontFamily.sans],
                body: ['Inter', ...defaultTheme.fontFamily.sans],
                mono: ['JetBrains Mono', ...defaultTheme.fontFamily.mono],
            },
            borderRadius: {
                lg: '20px',
                md: '14px',
                sm: '9px',
            },
        },
    },

    plugins: [forms],
};
