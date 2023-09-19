require("@rushstack/eslint-patch/modern-module-resolution");

module.exports = {
  root: true,
  env: {
    browser: true,
    es2021: true,
    node: true,
    jest: true,
  },
  extends: [
    "eslint:recommended",
    "plugin:vue/vue3-recommended",
    "@vue/eslint-config-typescript",
    "prettier",
  ],
  parser: "vue-eslint-parser",
  parserOptions: {
    parser: "@typescript-eslint/parser",
    ecmaVersion: 13,
    sourceType: "module",
  },
  plugins: ["vue"],
  rules: {
    "no-unused-vars": "off",
    "@typescript-eslint/no-unused-vars": "warn",
    "vue/multi-word-component-names": "off",
    "vue/attribute-hyphenation": ["error", "never"],
    "vue/v-on-event-hyphenation": ["error", "never"],
    "vue/component-definition-name-casing": "off",
    "vue/component-name-in-template-casing": [
      "warn",
      "PascalCase",
      {
        registeredComponentsOnly: false,
        ignores: ["component", "router-link", "router-view"],
      },
    ],
  },
  globals: {
    axios: true,
    Echo: true,
  },
  overrides: [
    // cypress should only be linted in cypress/
    {
      files: ["**/cypress/**/*.js"],
      extends: ["plugin:cypress/recommended"],
    },
    // this keeps jest from linting cypress files
    {
      files: ["**/resources/assets/js/**/*.js"],
      extends: ["plugin:jest/recommended", "plugin:jest/style"],
    },
  ],
};
