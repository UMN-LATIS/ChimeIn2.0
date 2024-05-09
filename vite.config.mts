import { defineConfig } from "vite";
import laravel from "laravel-vite-plugin";
import vue from "@vitejs/plugin-vue";

export default defineConfig({
  plugins: [
    laravel(["resources/assets/js/app.ts"]),
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
      // use vue's runtime compiler to support vue components
      // directly within blade templates
      vue: "vue/dist/vue.esm-bundler.js",
    },
  },
  // TODO: Remove this when we've migrated MIX variables to VITE
  // WARNING: DO NOT SET TO "" OR [] OR IT WILL EXPOSE ALL ENV VARS
  // just remove the line if you don't need it
  envPrefix: ["VITE_", "MIX_"],
});
