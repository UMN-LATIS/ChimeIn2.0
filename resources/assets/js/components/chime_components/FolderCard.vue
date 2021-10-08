<template>
  <div class="folder-card" data-cy="folder-card">
    <div class="folder-card__drag-handle" v-if="draggable">
      <i class="material-icons handle draghandle">drag_handle</i>
    </div>
    <router-link
      class="folder-card__name"
      :to="{
        name: 'folder',
        params: { chimeId: chime.id, folderId: folder.id },
      }"
    >
      <h2>
        {{ folder.name }}
      </h2>
    </router-link>
    <ul class="folder-card__utils">
      <li v-if="!!ltiLink" title="This folder is linked to Canvas">
        <a :href="ltiLink" target="_blank" rel="noopener noreferrer">
          <img
            class="canvas-logo"
            src="/img/canvas-logo.svg"
            alt="Linked to Canvas"
          />
        </a>
      </li>
      <li>
        <router-link
          :to="{
            name: 'present',
            params: { chimeId: chime.id, folderId: folder.id },
          }"
          class="text-dark"
        >
          <i class="material-icons">play_arrow</i>
        </router-link>
      </li>
    </ul>
  </div>
</template>

<script>
export default {
  props: ["folder", "chime", "draggable", "ltiLink"],
  data() {
    return {};
  },
};
</script>

<style>
.pointer {
  cursor: pointer;
}
.handle {
  cursor: move;
}
</style>
<style scoped>
a {
  color: #333;
}
.folder-card {
  border: 1px solid #ddd;
  border-radius: 0.25rem;
  display: flex;
  line-height: 1;
  margin-bottom: 1rem;
  overflow: hidden;
}
.folder-card:hover {
  box-shadow: 0 0.25rem 0.5rem hsla(0, 0%, 0%, 0.1);
  transition: ease-in-out 0.2s;
  border-color: #aaa;
}
.folder-card__drag-handle {
  align-items: center;
  display: flex;
  justify-content: center;
  padding: 0.5rem;
  background: #fafafa;
}
.folder-card__name {
  padding: 1.5rem;
  flex-grow: 1;
}
.folder-card__name h2 {
  margin: 0;
  font-size: 1.5rem;
}

.folder-card__utils {
  padding: 1rem;
  background: #fafafa;
  display: flex;
  justify-content: center;
  align-items: center;
}
.folder-card__utils li:not(:last-child) {
  margin-right: 1rem;
  border-right: 1px solid #fff;
}

.canvas-logo {
  width: 5rem;
}
ul {
  list-style: none;
  padding: 0;
  margin: 0;
}
</style>
