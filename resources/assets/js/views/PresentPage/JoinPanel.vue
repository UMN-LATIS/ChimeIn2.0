<template>
  <div class="join-panel">
    <header class="join-panel__header">
      <h2 class="join-panel__title">Join Chime</h2>
      <div class="join-panel__chime-name">
        <span v-if="isCanvasChime" class="badge badge-pill badge-dark">
          Canvas
        </span>
        {{ chime.name }}
      </div>
    </header>

    <div class="join-panel__instructions">
      <p v-if="isCanvasChime">
        Visit your Canvas course at: <a :href="canvasUrl">{{ canvasUrl }}</a>
      </p>
      <p v-else>
        Go to <a :href="location.origin">{{ location.host }}</a> and enter code
        <b>{{ toHyphenatedCode(chime.access_code) }}</b>
      </p>
    </div>
  </div>
</template>
<script>
import {
  selectCanvasCourseUrl,
  selectIsCanvasChime,
  selectJoinUrl,
} from "../../helpers/chimeSelectors.js";
import toHyphenatedCode from "../../helpers/toHyphenatedCode.js";

export default {
  props: {
    chime: {
      type: Object,
      required: true,
    },
  },
  methods: {
    toHyphenatedCode,
  },
  computed: {
    isCanvasChime() {
      return selectIsCanvasChime(this.chime);
    },
    canvasUrl() {
      return selectCanvasCourseUrl(this.chime);
    },
    joinUrl() {
      return selectJoinUrl(this.chime);
    },
    location() {
      return window.location;
    },
  },
};
</script>
<style scoped>
.join-panel {
  background: hsla(0, 0%, 100%, 0.85);
  backdrop-filter: blur(0.25rem);
  padding: 1rem;
  border-radius: 0.5rem;
  box-shadow: 0 0.5rem 0.5rem hsla(0, 0%, 0%, 0.1);
  font-size: 0.9rem;
  max-width: calc(100vw - 1rem);
  z-index: 100;
  width: 50vw;
  min-width: 20rem;
}

@media (max-width: 38rem) {
  .join-panel {
    width: 90vw;
  }
}
.join-panel__header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 0.5rem;
}
.join-panel__title {
  font-size: 0.9rem;
  font-weight: bold;
  text-transform: uppercase;
  margin: 0;
}
.join-panel__chime-name {
  white-space: nowrap;
  overflow: hidden;
  max-width: 50%;
  text-overflow: ellipsis;
}

p {
  margin: 0;
}
</style>
