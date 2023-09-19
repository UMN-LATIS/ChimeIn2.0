import { defineConfig } from "cypress";
import { addMatchImageSnapshotPlugin } from "@simonsmith/cypress-image-snapshot/plugin";

export default defineConfig({
  video: false,
  viewportWidth: 1920,
  viewportHeight: 1080,
  e2e: {
    setupNodeEvents(on) {
      on("before:browser:launch", (browser, launchOptions) => {
        if (browser.name === "chrome" && browser.isHeadless) {
          launchOptions.args.push("--force-device-scale-factor=1");
        }

        return launchOptions;
      });

      addMatchImageSnapshotPlugin(on);
    },
    baseUrl: "http://localhost",
    specPattern: "cypress/e2e/**/*.{test,spec}.js",
    experimentalRunAllSpecs: true,
  },
});
