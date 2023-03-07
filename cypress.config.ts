import { defineConfig } from "cypress";

export default defineConfig({
  video: false,
  viewportWidth: 1920,
  viewportHeight: 1080,
  // matchImageSnapshot: {
  //   failureThreshold: 0.1,
  //   failureThresholdType: 'percent',
  //   customDiffConfig: {
  //     threshold: 0.1,
  //   },
  //   capture: 'viewport',
  // },
  e2e: {
    // We've imported your old cypress plugins here.
    // You may want to clean this up later by importing these.
    setupNodeEvents(on, config) {
      return require("./cypress/plugins/index.ts")(on, config);
    },
    baseUrl: "http://localhost",
    specPattern: "cypress/e2e/**/*.{test,spec}.js",
    experimentalRunAllSpecs: true,
  },
});
