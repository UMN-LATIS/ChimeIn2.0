module.exports = {
  env: {
    browser: true,
    es2021: true,
    node: true,
    jest: true,
  },
  extends: ["eslint:recommended", "plugin:vue/essential", "prettier"],
  parserOptions: {
    ecmaVersion: 13,
    sourceType: "module",
  },
  plugins: ["vue"],
  rules: {},
  ignorePatterns: ["**/vendor/**/*.js", "**/public/**/*.js"],
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
