<template>
  <div class="join-panel">
    <header class="join-panel__header">
      <h2 class="join-panel__title">
        <i class="material-icons">how_to_reg</i>
        Join Chime
        <span
          v-if="isCanvasChime"
          data-cy="canvas-badge"
          class="badge badge-pill badge-gold"
        >
          Canvas
        </span>
      </h2>
    </header>

    <div v-if="isCanvasChime" class="join-panel__instructions">
      <p>Visit your course in Canvas:</p>
      <a :href="canvasUrl.origin" data-cy="canvas-host" target="_blank">{{
        canvasUrl.host
      }}</a>

      <details>
        <summary>Join Instructions for Ungraded Guests</summary>
        <ol>
          <li>
            Go to:
            <a data-cy="chime-host" :href="joinUrl">{{ joinUrl }}</a>
          </li>
          <li>
            Or: visit
            <a data-cy="chime-host" :href="joinUrl">{{ location.host }}</a> and
            enter:
            <b class="join-panel__access-code" data-cy="access-code">{{
              toHyphenatedCode(chime.access_code)
            }}</b>
          </li>
        </ol>
      </details>
    </div>

    <div v-else class="join-panel__instructions">
      <ol v-if="includeFullUrl">
        <li>
          Go to:
          <a data-cy="chime-host" :href="joinUrl">{{ joinUrl }}</a>
        </li>
        <li>
          Or: visit
          <a data-cy="chime-host" :href="joinUrl">{{ location.host }}</a> and
          enter
          <b class="join-panel__access-code" data-cy="access-code">{{
            toHyphenatedCode(chime.access_code)
          }}</b>
        </li>
      </ol>
      <ol v-else>
        <li>
          Go to:
          <a data-cy="chime-host" :href="location.origin">{{
            location.host
          }}</a>
        </li>
        <li>
          Enter Code
          <b class="join-panel__access-code" data-cy="access-code">{{
            toHyphenatedCode(chime.access_code)
          }}</b>
        </li>
      </ol>
    </div>
  </div>
</template>
<script>
import {
  selectCanvasCourseUrl,
  selectIsCanvasChime,
  selectJoinUrl,
} from "../helpers/chimeSelectors.js";
import toHyphenatedCode from "../helpers/toHyphenatedCode.js";

export default {
  props: {
    chime: {
      type: Object,
      required: true,
    },
    includeFullUrl: {
      type: Boolean,
      require: false,
    },
  },
  computed: {
    isCanvasChime() {
      return selectIsCanvasChime(this.chime);
    },
    canvasUrl() {
      const fullCanvasUrlString = selectCanvasCourseUrl(this.chime);
      return new URL(fullCanvasUrlString);
    },
    joinUrl() {
      return selectJoinUrl(this.chime);
    },
    location() {
      return window.location;
    },
  },
  methods: {
    toHyphenatedCode,
  },
};
</script>
<style scoped>
.join-panel {
  border: 1px solid #ddd;
  background: #f0f0f0;
  padding: 1rem;
  border-radius: 0.25rem;
  font-size: 0.9rem;
  min-width: 16rem;
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

.join-panel__access-code {
  display: inline-block;
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

ol {
  padding: 0;
  list-style: none;
  margin: 0;
}

details {
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
