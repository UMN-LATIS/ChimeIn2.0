import HomePage from "./pages/HomePage.vue";
import ChimePage from "./pages/ChimePage.vue";
import ChimeStudentPage from "./pages/ChimeStudentPage.vue";
import PresentPage from "./pages/PresentPage.vue";

const FolderPage = () =>
  import(
    /* webpackChunkName: "folder" */
    "./pages/FolderPage.vue"
  );

export default [
  { path: "/", component: HomePage },
  { path: "/chime/:chimeId", name: "chime", component: ChimePage, props: true },
  {
    path: "/chime/:chimeId/folder/:folderId",
    name: "folder",
    component: FolderPage,
    props: true,
  },
  {
    path: "/chimeParticipant/:chimeId/:folderId?",
    name: "chimeStudent",
    component: ChimeStudentPage,
    props: true,
  },
  {
    path: "/chime/:chimeId/folder/:folderId/present/:questionId?",
    name: "present",
    component: PresentPage,
    props: true,
  },
];
