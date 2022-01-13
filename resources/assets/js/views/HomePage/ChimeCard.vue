<template>
  <Card class="chime-card" v-show="showCard">
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

    <div v-if="canCurrentUserEdit" class="chime-card__join-details">
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

    <template #actions>
      <CardActionButton icon="clear" @click="removeChime(chime)" />
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
  data() {
    return {
      showCard: true,
    };
  },
  methods: {
    removeChime() {
      // if user can edit, this will delete the chime
      // if not, this will remove the user from the chime
      const confirmMessage = this.canCurrentUserEdit
        ? `Delete chime '${this.chime.name}'?`
        : `Are you sure you want to remove yourself from '${this.chime.name}'?`;

      const confirmation = window.confirm(confirmMessage);

      if (!confirmation) return;

      // optimistic UI: hide card unless failure
      this.showCard = false;

      axios
        .delete("/api/chime/" + this.chime.id, { timeout: 2000 })
        .then(() => this.$emit("change"))
        .catch((err) => {
          this.showCard = true;
          console.error("Error in removeChime request.", err);
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
