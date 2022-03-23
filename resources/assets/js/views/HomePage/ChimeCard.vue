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
          <a :href="canvasOrigin" target="_blank">{{ canvasOrigin }}</a>
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

    {{ canRemoveSelf }}
    <template #actions>
      <CardActionButton icon="clear" @click="toggleRemoveConfirmModal" />
    </template>

    <Portal>
      <Modal
        :show="isRemoveConfirmModalOpen"
        @close="toggleRemoveConfirmModal"
        class="chime-card__modal"
      >
        <div v-if="canRemoveSelf">
          <h2>Remove yourself?</h2>
          <p>You will no longer be able to access this chime.</p>
        </div>
        <div v-if="!canRemoveSelf">
          <h2>Delete Chime?</h2>
          <p>
            This chime with all of its folders, questions, and responses will be
            deleted.
          </p>
        </div>
        <div class="modal__button-group">
          <button
            v-if="canRemoveSelf"
            class="btn btn-danger modal__button"
            @click="handleRemoveSelf"
          >
            <i class="material-icons modal__button-icon">person_remove</i>
            Remove Myself
          </button>
          <button
            v-if="canCurrentUserEdit"
            class="btn modal__button"
            :class="{
              'btn-danger': isDeletePrimaryModalAction,
              'btn-outline-danger': !isDeletePrimaryModalAction,
            }"
            @click="handleDeleteChime"
          >
            <i class="material-icons modal__button-icon">delete</i>
            Delete Chime
          </button>
        </div>
      </Modal>
    </Portal>
  </Card>
</template>
<script>
import { Portal } from "@linusborg/vue-simple-portal";
import toHyphenatedCode from "../../helpers/toHyphenatedCode";
import Card from "../../components/Card.vue";
import CardActionButton from "../../components/CardActionButton.vue";
import Chip from "../../components/Chip.vue";
import {
  selectLtiReturnUrl,
  selectIsCanvasChime,
  selectJoinUrl,
} from "../../helpers/chimeSelectors.js";
import isPermittedOnChime from "../../helpers/isPermittedOnChime";
import { PERMISSIONS } from "../../helpers/constants";
import DetailsItem from "../../components/DetailsItem.vue";
import Modal from "../../components/Modal.vue";
import axios from "axios";

export default {
  components: {
    Card,
    CardActionButton,
    Chip,
    DetailsItem,
    Modal,
    Portal,
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
  emits: ["delete", "removeSelf"],
  data() {
    return {
      showCard: true,
      isRemoveConfirmModalOpen: false,
    };
  },
  computed: {
    canCurrentUserEdit() {
      return isPermittedOnChime(PERMISSIONS.EDIT, this.chime);
    },
    isPresenter() {
      return isPermittedOnChime(PERMISSIONS.PRESENT, this.chime);
    },
    isLastPresenter() {
      return this.isPresenter && this.chime.presenters_count < 2;
    },
    canRemoveSelf() {
      return !this.isLastPresenter;
    },
    isDeletePrimaryModalAction() {
      return !this.canRemoveSelf;
    },
    hyphenatedAccessCode() {
      return toHyphenatedCode(this.chime.access_code);
    },
    isCanvasChime() {
      return selectIsCanvasChime(this.chime);
    },
    ltiReturnUrl() {
      return selectLtiReturnUrl(this.chime);
    },
    canvasOrigin() {
      return new URL(this.ltiReturnUrl).origin;
    },
    joinUrl() {
      return selectJoinUrl(this.chime);
    },
    location() {
      return window.location;
    },
  },
  methods: {
    handleDeleteChime() {
      // optimistic UI update: hide card unless failure
      this.showCard = false;
      this.isRemoveConfirmModalOpen = false;

      axios
        .delete(`/api/chime/${this.chime.id}`, { timeout: 2000 })
        .then(() => this.$emit("change"))
        .catch((err) => {
          this.showCard = true;
          console.error("Error in removeChime request.", err);
        });
    },
    handleRemoveSelf() {
      // optimistic UI update: hide card unless failure
      this.showCard = false;
      this.isRemoveConfirmModalOpen = false;

      axios
        .delete(`/api/chime/${this.chime.id}/users/self`, { timeout: 2000 })
        .then((response) => console.log("response", response))
        // .then(() => this.$emit("change"))
        .catch((err) => {
          this.showCard = true;
          console.error("Error in removeChime request.", err);
        });
    },
    toggleRemoveConfirmModal() {
      this.isRemoveConfirmModalOpen = !this.isRemoveConfirmModalOpen;
    },
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

.modal__button-group {
  display: flex;
  gap: 0.5rem;
}

.modal__button {
  display: flex;
  justify-content: center;
  gap: 0.5rem;
  padding: 0.5rem 1rem;
}

@media (max-width: 38rem) {
  .modal__button-group {
    flex-direction: column;
  }
}
</style>
