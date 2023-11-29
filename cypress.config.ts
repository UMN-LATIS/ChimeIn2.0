import { defineConfig } from "cypress";
import { addMatchImageSnapshotPlugin } from "@simonsmith/cypress-image-snapshot/plugin";

const WIDTH = 1920;
const HEIGHT = 1080;

export default defineConfig({
  video: false,
  viewportWidth: WIDTH,
  viewportHeight: HEIGHT,
  retries: 2,
  e2e: {
    setupNodeEvents(on) {
      //docs.cypress.io/api/plugins/browser-launch-api#Set-screen-size-when-running-headless
      on("before:browser:launch", (browser, launchOptions) => {
        if (browser.name === "chrome" && browser.isHeadless) {
          launchOptions.args.push(`--window-size=${WIDTH},${HEIGHT}`);

          launchOptions.args.push("--force-device-scale-factor=1");
        }

        if (browser.name === "electron" && browser.isHeadless) {
          launchOptions.preferences.width = WIDTH;
          launchOptions.preferences.height = HEIGHT;
        }

        if (browser.name === "firefox" && browser.isHeadless) {
          launchOptions.args.push(`--width=${WIDTH}`);
          launchOptions.args.push(`--height=${HEIGHT}`);
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
