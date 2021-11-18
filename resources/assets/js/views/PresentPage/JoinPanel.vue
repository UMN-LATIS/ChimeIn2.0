<template>
  <div class="join-panel">
    <header class="join-panel__header">
      <h2 class="join-panel__title">
        <i class="material-icons">how_to_reg</i>
        Join Chime
        <span v-if="isCanvasChime" class="badge badge-pill badge-gold">
          Canvas
        </span>
      </h2>
    </header>

    <div v-if="isCanvasChime" class="join-panel__instructions">
      <ul>
        <li>
          Go to your Canvas course:
          <a :href="canvasUrl" class="external-link" target="_blank">{{
            canvasUrl
          }}</a>
        </li>
        <li v-if="folderName">
          Choose your ChimeIn assignment:
          <a :href="canvasUrl" class="external-link" target="_blank">{{
            folderName
          }}</a>
        </li>
      </ul>
      <details>
        <summary>Join Instructions for Ungraded Guests</summary>
        <p>
          Go to <a :href="location.origin">{{ location.host }}</a> and enter
          code
          <b>{{ toHyphenatedCode(chime.access_code) }}</b>
        </p>
      </details>
    </div>

    <div v-else class="join-panel__instructions">
      <p>
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
    folderName: {
      type: String,
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
  border: 1px solid #ddd;
  background: hsla(0, 0%, 95%, 0.9);
  backdrop-filter: blur(0.25rem);
  padding: 1rem;
  border-radius: 0.5rem;
  box-shadow: 0 0.25rem 0.5rem hsla(0, 0%, 0%, 0.1);
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
  display: flex;
  align-items: center;
}

.join-panel__title i {
  font-size: 1.5rem;
  margin-right: 0.5rem;
}

.external-link:after {
  font-family: "Material Icons";
  content: "launch";
  -webkit-font-feature-settings: "liga";
  font-feature-settings: "liga";
  font-size: 1em;
  vertical-align: middle;
  color: #777;
}
.external-link:hover {
  text-decoration: none;
}

.badge {
  margin: 0 0.25rem;
}

.join-panel__chime-name {
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}
p {
  margin: 0.5rem 0 0 0;
}

ul {
  padding-left: 1.5rem;
}

details {
  margin-left: 0.4rem;
  --details-padding: 0.75rem;
  font-size: 0.75rem;
  color: #777;
  margin-top: 0.5rem;
  padding-left: var(--details-padding);
}

summary {
  margin-left: calc(-1 * var(--details-padding));
}

details[open] summary {
  margin-bottom: 0.5rem;
}
</style>
