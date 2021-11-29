<template>
  <Card
    class="chime-card"
    :icon="showMoveIcon ? 'drag_handle' : ''"
    iconClass="handle"
    @click.native="$router.push(to)"
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
    </router-link>

    <div v-if="canCurrentUserEdit">
      <div v-if="isCanvasChime">
        <DetailsItem>
          <template #label>Join</template>
          <a :href="canvasUrl.origin" target="_blank">{{ canvasUrl.host }}</a>
        </DetailsItem>
      </div>

      <div v-if="!isCanvasChime">
        <DetailsItem>
          <template #label>Access&nbsp;Code</template>
          {{ hyphenatedAccessCode }}
        </DetailsItem>
        <DetailsItem>
          <template #label>Join</template>
          {{ joinUrl }}
        </DetailsItem>
      </div>
    </div>

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
import DetailsItem from "../../components/DetailsItem.vue";

export default {
  components: {
    Card,
    CardActionButton,
    Chip,
    DetailsItem,
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
  margin-bottom: 1rem;
}
.chime-card__chip-group {
  margin-left: 1rem;
}
</style>
