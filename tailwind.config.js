import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/*
 * Palet merek NetBill: biru-teal (aksen utama) + navy + emas.
 * Kelas lama `sky-*` & `blue-*` dipetakan ke biru-teal supaya semua halaman
 * otomatis senada dengan logo & beranda tanpa mengubah tiap halaman.
 */
const brand = {
    50: '#ecfbfa',
    100: '#d0f4f1',
    200: '#a6e8e4',
    300: '#6fd5d2',
    400: '#36b9bb',
    500: '#189ca2',
    600: '#117e88',
    700: '#13656f',
    800: '#16525b',
    900: '#17444d',
    950: '#082a32',
};

const navy = {
    50: '#f2f4fb',
    100: '#e3e7f5',
    200: '#c6cdea',
    300: '#9aa6d6',
    400: '#6878bd',
    500: '#4757a3',
    600: '#364487',
    700: '#2c376d',
    800: '#1f2a57',
    900: '#13204a',
    950: '#0b1026',
};

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
                sans: ['"Plus Jakarta Sans"', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                brand,
                navy,
                sky: brand,
                blue: brand,
                gold: { 300: '#f6d27a', 400: '#eebd52', 500: '#e0a43a', 600: '#c4862a' },
            },
        },
    },

    plugins: [forms],
};
