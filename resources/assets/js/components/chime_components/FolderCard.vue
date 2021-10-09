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
      <li v-if="!!ltiLink" title="This is a Canvas Assignment">
        <a :href="ltiLink" target="_blank" rel="noopener noreferrer">
          <span class="badge badge-pill badge-dark">Canvas Assignment</span>
        </a>
      </li>
      <li class="folder-card__action">
        <router-link
          class="folder-card__name"
          :to="{
            name: 'folder',
            params: { chimeId: chime.id, folderId: folder.id },
          }"
        >
          <span class="folder-card__action-label">Edit</span>
          <i class="material-icons m-1">edit</i>
        </router-link>
      </li>
      <li class="folder-card__action">
        <router-link
          :to="{
            name: 'present',
            params: { chimeId: chime.id, folderId: folder.id },
          }"
        >
          <span class="folder-card__action-label">Present</span>
          <i class="material-icons m-1">play_circle_outline</i>
        </router-link>
      </li>
    </ul>
  </div>
</template>

<script>
export default {
  props: ["folder", "chime", "draggable"],
  data() {
    return {
      ltiLink: "http://canvas.umn.edu",
    };
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
.badge {
  font-weight: normal;
  padding: 0.25rem 0.5rem;
  background: var(--gold-light);
  color: #333;
  border: 1px solid var(--gold-dark);
}
a {
  color: #333;
}

a:hover {
  text-decoration: none;
}

.flex {
  display: flex;
}
.flex-center {
  align-items: center;
}
.folder-card {
  /* border: 1px solid #ddd; */
  background: #eaeaea;
  border-radius: 0.25rem;
  display: flex;
  /* align-items: center; */
  line-height: 1;
  margin-bottom: 1rem;
  overflow: hidden;
}
.folder-card:hover {
  box-shadow: 0 0.25rem 0.5rem hsla(0, 0%, 0%, 0.2);
  transition: ease-in-out 0.3s;
  border-color: #aaa;
}
.folder-card__drag-handle {
  align-items: center;
  display: flex;
  justify-content: center;
  padding: 0.5rem;
  padding-left: 1rem;
}
.folder-card__name {
  padding: 1.5rem 0.5rem;
  flex-grow: 1;
}
.folder-card__name h2 {
  margin: 0;
  font-size: 1.5rem;
}

.folder-card__utils {
  display: flex;
}
.folder-card__utils {
  padding: 0;
}

.folder-card__action {
  background: #fafafa;
}

.folder-card i {
  font-size: 1.5rem;
}
.folder-card__action-label {
  font-size: 0.8rem;
}
.folder-card__utils a {
  height: 100%;
  padding: 1rem;
  display: flex;
  flex-direction: column-reverse;
  align-items: center;
  justify-content: center;
  text-decoration: none;
}

ul {
  list-style: none;
  padding: 0;
  margin: 0;
}
</style>
