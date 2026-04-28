const defaultTheme = require("tailwindcss/defaultTheme");
const forms = require("@tailwindcss/forms");

/** @type {import('tailwindcss').Config} */
module.exports = {
    darkMode: "class",
    content: [
        "./vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php",
        "./storage/framework/views/*.php",
        "./resources/views/**/*.blade.php",
    ],
    theme: {
        extend: {
            colors: {
                brand: {
                    DEFAULT: "#157547",
                    green: "#157547",
                    lime: "#A1CD3A",
                },
            },
            fontFamily: {
                sans: ["Inter", ...defaultTheme.fontFamily.sans],
            },
        },
    },
    plugins: [forms],
};
