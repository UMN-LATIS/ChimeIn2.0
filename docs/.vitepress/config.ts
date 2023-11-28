import { defineConfig } from "vitepress";

export default defineConfig({
  lang: "en-US",
  title: "ChimeIn Help",
  base: "/ChimeIn2.0/",
  lastUpdated: true,
  appearance: false, // force light mode. UMN header looks bad in dark mode
  themeConfig: {
    sidebar: [
      {
        text: "Getting Started",
        items: [
          {
            text: "Introduction",
            link: "/",
          },
          {
            text: "Participating",
            link: "/participate",
          },
          {
            text: "Creating Questions",
            link: "/asking-questions",
          },
          {
            text: "Using with Canvas",
            link: "/canvas",
          },
          {
            text: "Cheatsheet",
            link: "/cheatsheet",
          },
          {
            text: "Accessibility",
            link: "/accessibility",
          },
        ],
      },
      {
        text: "Tips",
        items: [
          {
            text: "Taking Attendance",
            link: "/taking-attendance",
          },
          {
            text: "Managing Chimes",
            link: "/managing-a-chime",
          },
          {
            text: "Asynchronous Usage",
            link: "/async",
          },
        ],
      },
    ],
    socialLinks: [
      { icon: "github", link: "https://github.com/UMN-LATIS/ChimeIn2.0" },
    ],
    editLink: {
      pattern:
        "https://github.com/UMN-LATIS/ChimeIn2.0/edit/develop/docs/:path",
      text: "Edit this page on GitHub",
    },
    footer: {
      message: "Released under the MIT License.",
      copyright: "Copyright © 2019-present Evan You",
    },
    search: {
      provider: "local",
    },
  },
});
