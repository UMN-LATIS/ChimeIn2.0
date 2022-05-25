import { createRouter, createWebHistory } from "vue-router";
import HomePage from "./views/HomePage/HomePage.vue";
import ChimePage from "./views/ChimePage/ChimePage.vue";
import FolderPage from "./views/FolderPage/FolderPage.vue";
import ParticipantPage from "./views/ParticipantPage/ParticipantPage.vue";
import PresentPage from "./views/PresentPage/PresentPage.vue";

const toInt = (value, fallback = undefined) => {
  const n = Number.parseInt(value);
  return Number.isNaN(n) ? fallback : n;
};

const routes = [
  { path: "/", component: HomePage },
  {
    path: "/chime/:chimeId",
    name: "chime",
    component: ChimePage,
    props: (route) => ({
      chimeId: toInt(route.params.chimeId),
    }),
  },
  {
    path: "/chime/:chimeId/folder/:folderId",
    name: "folder",
    component: FolderPage,
    props: (route) => ({
      chimeId: toInt(route.params.chimeId),
      folderId: toInt(route.params.folderId),
    }),
  },
  {
    path: "/chimeParticipant/:chimeId/:folderId?",
    name: "chimeStudent",
    component: ParticipantPage,
    props: (route) => ({
      chimeId: toInt(route.params.chimeId),
      folderId: toInt(route.params.folderId, 0),
    }),
  },
  {
    path: "/chime/:chimeId/folder/:folderId/present/:questionIndex?",
    name: "present",
    component: PresentPage,
    props: (route) => ({
      chimeId: toInt(route.params.chimeId),
      folderId: toInt(route.params.folderId),
      questionIndex: toInt(route.params.questionIndex, 0),
    }),
  },
];

const router = createRouter({
  history: createWebHistory(),
  routes,
});

export default router;
