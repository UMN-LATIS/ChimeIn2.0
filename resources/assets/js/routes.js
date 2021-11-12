import Home from "./components/Home.vue";
import Chime from "./components/Chime.vue";
import ChimeStudent from "./components/Chime_student.vue";
import Present from "./components/Present.vue";

const Folder = () =>
  import(
    /* webpackChunkName: "folder" */
    "./components/Folder.vue"
  );

export default [
  { path: "/", component: Home },
  { path: "/chime/:chimeId", name: "chime", component: Chime, props: true },
  {
    path: "/chime/:chimeId/folder/:folderId",
    name: "folder",
    component: Folder,
    props: true,
  },
  {
    path: "/chimeParticipant/:chimeId/:folderId?",
    name: "chimeStudent",
    component: ChimeStudent,
    props: true,
  },
  {
    path: "/chime/:chimeId/folder/:folderId/present/:questionId?",
    name: "present",
    component: Present,
    props: true,
  },
];
