import { umnTheme } from "./umn-theme/index.ts";

export default {
  lang: "en-US",
  title: "ChimeIn Help",
  base: "/ChimeIn2.0/",
  theme: umnTheme({
    repo: "umn-latis/ChimeIn2.0",
    repoLabel: "Contribute!",
    docsDir: "docs",
    docsBranch: "develop",
    editLinks: true,
    editLinkText: "Help us improve this page!",
    sidebar: {
      "/": [
        {
          text: "Guide",
          children: [
            "/",
            "/participate",
            "/asking-questions",
            "/canvas",
            "/taking-attendance",
            "/managing-a-chime",
            "/async",
            "/accessibility",
            "/moving-to-chimein-2",
            "/deploying",
            "/cheatsheet",
          ],
        },
      ],
    },
  }),
  plugins: [
    [
      {
        selector: "img",
        delay: 300,
        options: {
          margin: 24,
          background: "rgba(100, 100, 100, 0.65)",
          bgOpacity: "0.6",
          scrollOffset: 0,
        },
      },
    ],
  ],
};
