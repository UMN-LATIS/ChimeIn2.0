import { createRouter, createWebHistory, RouteRecordRaw } from "vue-router";
import HomePage from "./views/HomePage/HomePage.vue";
import ChimePage from "./views/ChimePage/ChimePage.vue";
import FolderPage from "./views/FolderPage/FolderPage.vue";
import ParticipantPage from "./views/ParticipantPage/ParticipantPage.vue";
import PresentPage from "./views/PresentPage/PresentPage.vue";
import NotFoundPage from "./views/NotFoundPage/NotFoundPage.vue";
import FolderParticipationPage from "./views/FolderParticipationPage/FolderParticipationPage.vue";

const toInt = (value: string | string[], fallback?: number) => {
  if (Array.isArray(value) && !value.length) {
    return fallback;
  }

  const n: number = Array.isArray(value)
    ? Number.parseInt(value[0])
    : Number.parseInt(value);
  return Number.isNaN(n) ? fallback : n;
};

const routes: RouteRecordRaw[] = [
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
    path: "/chime/:chimeId/folder/:folderId/participation",
    name: "folderParticipationReport",
    component: FolderParticipationPage,
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
  { path: "/:pathMatch(.*)*", name: "NotFound", component: NotFoundPage },
];

const router = createRouter({
  history: createWebHistory(),
  routes,
});

export default router;
