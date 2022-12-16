import { defineConfig } from "vite";
import laravel from "laravel-vite-plugin";
import vue from "@vitejs/plugin-vue";

export default defineConfig({
  plugins: [
    // TODO: Move CSS entry point to js for a better dev experience
    laravel(["resources/assets/sass/app.scss", "resources/assets/js/app.ts"]),
    vue({
      template: {
        transformAssetUrls: {
          base: null,
          includeAbsolute: false,
        },
      },
    }),
  ],
  resolve: {
    alias: {
      "@": "/resources/assets/js",
    },
  },
  // TODO: Remove this when we've migrated MIX variables to VITE
  // WARNING: DO NOT SET TO "" OR [] OR IT WILL EXPOSE ALL ENV VARS
  // just remove the line if you don't need it
  envPrefix: ["VITE_", "MIX_"],
});
