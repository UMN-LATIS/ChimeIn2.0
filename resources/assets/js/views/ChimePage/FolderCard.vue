<template>
  <Card
    v-if="isFolderVisible"
    class="folder-card"
    data-cy="folder-card"
    :icon="showMoveIcon ? 'drag_handle' : ''"
    icon-class="handle"
  >
    <router-link :to="to">
      <div class="folder-card__contents">
        <h2 class="folder-card__name">
          {{ folder.name }}
        </h2>
        <div>
          <Chip
            :color="folder.questions_count ? 'dark' : 'muted'"
            :solid="true"
          >
            {{ folder.questions_count }}
            {{ pluralize("Question", folder.questions_count) }}
          </Chip>
        </div>
      </div>
    </router-link>
    <template #actions>
      <CardActionButton icon="edit" :to="to"> Edit </CardActionButton>
      <CardActionButton icon="play_circle_outline" :to="`${to}/present`">
        Present
      </CardActionButton>
      <CardActionButton
        data-cy="delete-folder-button"
        icon="clear"
        @click="handleClickDelete"
      >
        Delete
      </CardActionButton>
    </template>

    <Teleport to="body">
      <Modal
        :show="isRemoveConfirmModalOpen"
        class="folder-card__modal"
        @close="handleCloseModal"
      >
        <h2>Delete Folder?</h2>
        <p>
          <i>{{ folder.name }}</i> will be deleted, including all of its
          questions and responses.
        </p>
        <div class="modal__button-group">
          <button
            class="btn btn-danger modal__button d-inline-flex align-items-center"
            data-cy="confirm-delete-button"
            @click="handleDeleteFolder"
          >
            <i class="material-icons modal__button-icon mr-2">delete</i>
            Delete Folder
          </button>
        </div>
      </Modal>
    </Teleport>
  </Card>
</template>
<script>
import Card from "../../components/Card.vue";
import CardActionButton from "../../components/CardActionButton.vue";
import Chip from "../../components/Chip.vue";
import Modal from "../../components/Modal.vue";
import pluralize from "../../common/pluralize.js";

export default {
  components: {
    Card,
    CardActionButton,
    Chip,
    Modal,
  },
  props: {
    chime: {
      type: Object,
      required: true,
    },
    folder: {
      type: Object,
      required: true,
    },
    showMoveIcon: {
      type: Boolean,
      default: false,
    },
  },
  emits: ["change"],
  data() {
    return {
      isFolderVisible: true,
      isRemoveConfirmModalOpen: false,
    };
  },
  computed: {
    to() {
      return `/chime/${this.chime.id}/folder/${this.folder.id}`;
    },
  },
  methods: {
    pluralize,
    handleClickDelete() {
      this.isRemoveConfirmModalOpen = true;
    },
    handleCloseModal() {
      this.isRemoveConfirmModalOpen = false;
    },
    handleDeleteFolder() {
      const chimeId = this.folder.chime_id;
      const folderId = this.folder.id;

      // optimistic UI: hide folder
      this.isFolderVisible = false;
      this.isRemoveConfirmModalOpen = false;

      axios
        .delete(`/api/chime/${chimeId}/folder/${folderId}`)
        .then(() => this.$emit("change"))
        .catch((err) => {
          console.error(err);
          this.isFolderVisible = true;
        });
    },
  },
};
</script>

<style scoped>
.folder-card {
  align-items: center;
}

.folder-card__contents {
  display: grid;
  grid-template-columns: 1fr auto;
  gap: 0.5rem;
}

@media (max-width: 48rem) {
  .folder-card__contents {
    display: flex;
    flex-direction: column;
  }
}
</style>
