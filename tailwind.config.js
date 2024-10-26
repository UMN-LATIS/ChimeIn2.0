/** @type {import('tailwindcss').Config} */
export default {
  prefix: "tw-",
  corePlugins: {
    preflight: false,
  },
  content: [
    "./resources/**/*.blade.php",
    "./resources/**/*.js",
    "./resources/**/*.vue",
  ],
  theme: {
    extend: {
      colors: {
        "umn-maroon": "#7a0019",
        "umn-gold": "#ffcc33",
        "umn-gold-light": "#ffde7a",
        "umn-neutral-50": "#f9f7f6", // page background color
        "umn-neutral-100": `#f0efee`, // gray for navbar
        "umn-neutral-200": `#d5d6d2`,
        "umn-neutral-500": `#737487`,
        "umn-neutral-700": `#5a5a5a`, // text color
        "umn-neutral-800": `#262626`, // active text color
        "umn-neutral-900": `#1a1a1a`,
        "bs-blue": "#3490dc",
      },
    },
  },
  plugins: [
    require("@tailwindcss/forms")({
      strategy: "class", // only generate classes
    }),
  ],
};
