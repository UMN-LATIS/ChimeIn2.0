import type { Theme } from "@vuepress/core";
import { defaultTheme } from "@vuepress/theme-default";
import type { DefaultThemeOptions } from "@vuepress/theme-default";
import { path } from "@vuepress/utils";

export const umnTheme = (options) => {
  return {
    name: "vuepress-theme-umn",
    extends: defaultTheme(options),

    layouts: {
      Layout: path.resolve(__dirname, "layouts/Layout.vue"),
      404: path.resolve(__dirname, "layouts/404.vue"),
    },
  };
};
