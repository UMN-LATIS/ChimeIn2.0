/** @type {import('ts-jest/dist/types').InitialOptionsTsJest} */
export default {
  preset: "ts-jest",
  testEnvironment: "jsdom",
  testMatch: [
    "**/resources/assets/js/**/__tests__/**/*.?(m)[jt]s?(x)",
    "**/resources/assets/js/**/?(*.)+(spec|test).?(m)[tj]s?(x)",
  ],
};
