import { createRouter, createWebHistory } from "vue-router";
import HomePage from "./views/HomePage/HomePage.vue";
import ChimePage from "./views/ChimePage/ChimePage.vue";
import ParticipantPage from "./views/ParticipantPage/ParticipantPage.vue";
import PresentPage from "./views/PresentPage/PresentPage.vue";

const FolderPage = () =>
  import(
    /* webpackChunkName: "folder" */
    "./views/FolderPage/FolderPage.vue"
  );

const routes = [
  { path: "/", component: HomePage },
  {
    path: "/chime/:chimeId",
    name: "chime",
    component: ChimePage,
    props: true,
  },
  {
    path: "/chime/:chimeId/folder/:folderId",
    name: "folder",
    component: FolderPage,
    props: true,
  },
  {
    path: "/chimeParticipant/:chimeId/:folderId?",
    name: "chimeStudent",
    component: ParticipantPage,
    props: true,
  },
  {
    path: "/chime/:chimeId/folder/:folderId/present/:questionId?",
    name: "present",
    component: PresentPage,
    props: true,
  },
];

const router = createRouter({
  history: createWebHistory(),
  routes,
});

export default router;
