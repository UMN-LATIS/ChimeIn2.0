module.exports = {
  plugins: ["vuepress-plugin-export"],
  title: "ChimeIn Documentation",
  base: "/ChimeIn2.0/",
  themeConfig: {
    // Assumes GitHub. Can also be a full GitLab url.

    repo: "umn-latis/ChimeIn2.0",
    // Customising the header label
    // Defaults to "GitHub"/"GitLab"/"Bitbucket" depending on `themeConfig.repo`
    repoLabel: "Contribute!",
    // Optional options for generating "Edit this page" link
    // if your docs are not at the root of the repo:
    docsDir: "docs",
    // if your docs are in a specific branch (defaults to 'master'):
    docsBranch: "develop",
    // defaults to false, set to true to enable
    editLinks: true,
    // custom text for edit link. Defaults to "Edit this page"
    editLinkText: "Help us improve this page!",
    sidebar: [
      "/",
      "/cheatsheet",
      "/participate",
      "/asking-questions",
      "/canvas",
      "/taking-attendance",
      "/managing-a-chime",
      "/async",
      "/accessibility",
      "/moving-to-chimein-2",
      "/deploying",
    ],
  },
  plugins: [
    [
      "vuepress-plugin-medium-zoom",
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
