import defaultTheme from 'tailwindcss/defaultTheme';
import tailwindCssPrime from 'tailwindcss-primeui';

/** @type {import('tailwindcss').Config} */
export default {
	content: [
		'./vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './views/**/*.blade.php',
		'./storage/framework/views/*.php',
		'./resources/**/*.blade.php',
		'./resources/**/*.js',
		'./resources/**/*.vue',
		'./resources/views/login.blade.php'
	],
	// darkMode: ['dark'],
	darkMode: ['variant', [
		'&:is(.dark *)',
    ]],
	theme: {
		extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
		},
	},
	plugins: [tailwindCssPrime],
};
