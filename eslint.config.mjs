import pluginVue from "eslint-plugin-vue";
import vueTsConfig from "@vue/eslint-config-typescript";
import prettierConfig from "eslint-config-prettier";
import jestPlugin from "eslint-plugin-jest";
import cypressPlugin from "eslint-plugin-cypress";

export default [
  {
    ignores: [
      "vendor/**",
      "public/**",
      "node_modules/**",
      "resources/assets/cla-vue-template/**",
    ],
  },

  ...pluginVue.configs["flat/recommended"],
  ...vueTsConfig(),
  prettierConfig,

  {
    languageOptions: {
      ecmaVersion: 2022,
      sourceType: "module",
      globals: {
        axios: "readonly",
        Echo: "readonly",
      },
    },
    rules: {
      "no-unused-vars": "off",
      "no-var": "warn",
      "prefer-const": "warn",
      "@typescript-eslint/no-unused-vars": "warn",
      "@typescript-eslint/no-explicit-any": "warn",
      "@typescript-eslint/no-unused-expressions": "warn",
      "@typescript-eslint/no-empty-object-type": "off",
      "@typescript-eslint/ban-ts-comment": "warn",
      "vue/multi-word-component-names": "off",
      "vue/attribute-hyphenation": ["error", "never"],
      "vue/v-on-event-hyphenation": ["error", "never"],
      "vue/component-definition-name-casing": "off",
      "vue/no-unused-vars": "warn",
      "vue/block-lang": "off",
      "vue/component-name-in-template-casing": [
        "warn",
        "PascalCase",
        {
          registeredComponentsOnly: false,
          ignores: ["component", "router-link", "router-view"],
        },
      ],
    },
  },

  {
    files: ["**/*.js", "**/*.cjs", "**/*.mjs"],
    rules: {
      "@typescript-eslint/no-require-imports": "off",
    },
  },

  {
    files: ["**/cypress/**/*.js"],
    ...cypressPlugin.configs.recommended,
  },

  {
    files: [
      "**/resources/assets/js/**/*.test.*",
      "**/resources/assets/js/**/*.spec.*",
    ],
    ...jestPlugin.configs["flat/recommended"],
    ...jestPlugin.configs["flat/style"],
    rules: {
      ...jestPlugin.configs["flat/recommended"].rules,
      ...jestPlugin.configs["flat/style"].rules,
      "jest/prefer-to-be": "warn",
      "jest/prefer-to-have-length": "warn",
    },
  },
];
