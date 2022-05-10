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
    props: (route) => ({
      chimeId: Number.parseInt(route.params.chimeId),
    }),
  },
  {
    path: "/chime/:chimeId/folder/:folderId",
    name: "folder",
    component: FolderPage,
    props: (route) => ({
      chimeId: Number.parseInt(route.params.chimeId),
      folderId: Number.parseInt(route.params.folderId),
    }),
  },
  {
    path: "/chimeParticipant/:chimeId/:folderId?",
    name: "chimeStudent",
    component: ParticipantPage,
    props: (route) => ({
      chimeId: Number.parseInt(route.params.chimeId),
      folderId: Number.parseInt(route.params.folderId),
    }),
  },
  {
    path: "/chime/:chimeId/folder/:folderId/present/:questionIndex?",
    name: "present",
    component: PresentPage,
    props: (route) => ({
      chimeId: Number.parseInt(route.params.chimeId),
      folderId: Number.parseInt(route.params.folderId),
      questionIndex: Number.parseInt(route.params.questionIndex),
    }),
  },
];

const router = createRouter({
  history: createWebHistory(),
  routes,
});

export default router;
