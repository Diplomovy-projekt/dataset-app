import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './vendor/robsontenorio/mary/src/View/Components/**/*.php',
        './resources/**/*.js',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                'hcportal-primary': '#2c3e50', // custom color for hcportal-primary
            },
            screens: {
                'xs': '400px',   // custom breakpoint for 'xs'
                'sm': '576px',   // sm breakpoint
                'md': '768px',   // md breakpoint
                'lg': '992px',   // lg breakpoint
                'xl': '1140px',  // xl breakpoint
            },
            container: {
                center: true, // Centers the container
                screens: {
                    sm: '540px',  // max-width for sm breakpoint (576px)
                    md: '720px',  // max-width for md breakpoint (768px)
                    lg: '960px',  // max-width for lg breakpoint (992px)
                    xl: '1140px', // max-width for xl breakpoint (1200px)
                },
            },
        },
    },

    plugins: [
        require("daisyui"), // include daisyui plugin
    ],
};
