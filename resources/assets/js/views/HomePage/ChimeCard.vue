<template>
  <Card
    class="chime-card"
    :icon="showMoveIcon ? 'drag_handle' : 'arrow_forward'"
    iconClass="handle"
  >
    <router-link :to="to">
      <header class="chime-card__header">
        <h1 class="chime-card__title">
          {{ chime.name }}
        </h1>
        <div class="chime-card__chip-group">
          <Chip v-if="isCanvasChime" color="yellow" :solid="true">Canvas</Chip>
        </div>
      </header>

      <div v-if="canCurrentUserEdit">
        <dl v-if="isCanvasChime" class="chime-card__details-list">
          <dt>Join</dt>
          <dd>
            <a :href="canvasUrl.origin" target="_blank">{{ canvasUrl.host }}</a>
          </dd>
        </dl>

        <dl v-if="!isCanvasChime" class="chime-card__details-list">
          <dt>Access&nbsp;Code</dt>
          <dd>{{ hyphenatedAccessCode }}</dd>
          <dt>Join</dt>
          <dd>
            <a :href="joinUrl" target="_blank">{{ joinUrl }}</a>
          </dd>
        </dl>
      </div>
    </router-link>

    <template #actions v-if="canCurrentUserEdit">
      <CardActionButton icon="clear" @click="handleChimeDelete(chime)" />
    </template>
  </Card>
</template>
<script>
import toHyphenatedCode from "../../helpers/toHyphenatedCode";
import Card from "../../components/Card.vue";
import CardActionButton from "../../components/CardActionButton.vue";
import Chip from "../../components/Chip.vue";
import {
  selectCanvasCourseUrl,
  selectIsCanvasChime,
  selectJoinUrl,
} from "../../helpers/chimeSelectors.js";
import isPermittedOnChime from "../../helpers/isPermittedOnChime";
import { PERMISSIONS } from "../../helpers/constants";

export default {
  components: {
    Card,
    CardActionButton,
    Chip,
  },
  props: {
    chime: {
      type: Object,
      required: true,
    },
    to: {
      type: String,
      required: true,
    },
    showMoveIcon: {
      type: Boolean,
      default: false,
    },
  },
  methods: {
    handleChimeDelete() {
      const confirmation = window.confirm(`Delete chime '${this.chime.name}'?`);

      if (!confirmation) return;

      axios
        .delete("/api/chime/" + this.chime.id)
        .then(() => this.$emit("change"))
        .catch((err) => {
          console.error("error", "Error in delete chime:", err.response);
        });
    },
  },
  computed: {
    canCurrentUserEdit() {
      return isPermittedOnChime(PERMISSIONS.EDIT, this.chime);
    },
    hyphenatedAccessCode() {
      return toHyphenatedCode(this.chime.access_code);
    },
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
  mounted() {
    // this.chime.lti_return_url = `https://mock-canvas.umn.edu/courses/123456`;
  },
};
</script>

<style scoped>
.chime-card__header {
  display: flex;
  align-items: center;
}
.chime-card__chip-group {
  margin-left: 1rem;
}

.chime-card__details-list {
  display: grid;
  grid-template-columns: min-content 1fr;
  column-gap: 1rem;
  font-size: 0.9rem;
  align-items: baseline;
  margin: 0;
  margin-top: 0.5rem;
}

.chime-card__details-list dt {
  font-weight: normal;
  text-transform: uppercase;
  color: #aaa;
  font-size: 0.7rem;
}
.chime-card__details-list dd,
.chime-card__details-list a {
  color: #777;
}
</style>
