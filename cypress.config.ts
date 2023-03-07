import { defineConfig } from "cypress";
import { addMatchImageSnapshotPlugin } from "@simonsmith/cypress-image-snapshot/plugin";

export default defineConfig({
  video: false,
  viewportWidth: 1920,
  viewportHeight: 1080,
  e2e: {
    setupNodeEvents(on, config) {
      addMatchImageSnapshotPlugin(on, config);
    },
    baseUrl: "http://localhost",
    specPattern: "cypress/e2e/**/*.{test,spec}.js",
    experimentalRunAllSpecs: true,
  },
});
