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

    <template v-if="isCanvasChime">
      <QRCode v-if="canvasUrl" :url="canvasUrl.href" />
      <p class="tw-text-center !tw-mb-6">
        Find your assignment in Canvas
        <a data-cy="chime-host" :href="canvasUrl?.host" class="tw-font-mono">{{
          canvasUrl?.host
        }}</a
        >.
      </p>

      <details class="tw-bg-black/5 tw-rounded">
        <summary
          class="tw-bg-black/5 tw-px-2 tw-py-1 tw-flex tw-justify-center tw-rounded"
        >
          Instructions for Ungraded Guest
        </summary>
        <div class="join-panel__instructions">
          <p>
            Guests may also join this Chime. They will NOT recieve grades in
            Canvas if they join with the code below.
          </p>
          <p class="tw-text-center">
            Visit
            <a data-cy="chime-host" :href="joinUrl">{{ location.host }}</a> and
            enter code
            <b
              class="tw-font-mono tw-flex tw-justify-center tw-text-2xl"
              data-cy="access-code"
              >{{ toHyphenatedCode(chime.access_code) }}</b
            >
          </p>
        </div>
      </details>
    </template>

    <template v-else>
      <QRCode :url="joinUrl" />

      <div class="join-panel__instructions">
        <p class="tw-text-center">
          Visit
          <a data-cy="chime-host" :href="joinUrl">{{ location.host }}</a> and
          enter code
          <b
            class="tw-font-mono tw-flex tw-justify-center tw-text-2xl"
            data-cy="access-code"
            >{{ toHyphenatedCode(chime.access_code) }}</b
          >
        </p>
      </div>
    </template>
  </div>
</template>
<script setup lang="ts">
import { computed, ref } from "vue";
import {
  selectCanvasCourseUrl,
  selectIsCanvasChime,
  selectJoinUrl,
} from "@/helpers/chimeSelectors";
import toHyphenatedCode from "@/helpers/toHyphenatedCode";
import QRCode from "@/components/QRCode.vue";
import { Chime } from "@/types";

const props = defineProps<{
  chime: Chime;
}>();

const isJoinForUngradedOpen = ref(false);

const isCanvasChime = computed(() => selectIsCanvasChime(props.chime));

const canvasUrl = computed(() => {
  const fullCanvasUrlString = selectCanvasCourseUrl(props.chime);

  if (!fullCanvasUrlString) return null;
  return new URL(fullCanvasUrlString);
});

const joinUrl = computed(() => selectJoinUrl(props.chime));

const { location } = window;
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
