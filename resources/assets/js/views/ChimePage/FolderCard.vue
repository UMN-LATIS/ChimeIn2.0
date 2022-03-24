<template>
  <Card
    class="folder-card"
    data-cy="folder-card"
    :icon="showMoveIcon ? 'drag_handle' : ''"
    iconClass="handle"
    v-if="isFolderVisible"
  >
    <div class="folder-card__contents">
      <router-link :to="to">
        <h2>
          {{ folder.name }}
        </h2>
      </router-link>
      <Chip :color="folder.questions_count ? 'dark' : 'muted'" :solid="true">
        <span v-if="folder.questions_count">
          {{ folder.questions_count }}
          {{ "Question" | pluralize(folder.questions_count) }}
        </span>
        <span v-else>No Questions</span>
      </Chip>
    </div>
    <template #actions>
      <CardActionButton icon="edit" :to="to"> Edit </CardActionButton>
      <CardActionButton icon="play_circle_outline" :to="`${to}/present`">
        Present
      </CardActionButton>
      <CardActionButton
        data-cy="delete-folder-button"
        icon="clear"
        @click="handleDeleteClick"
      >
        Delete
      </CardActionButton>
    </template>
  </Card>
</template>
<script>
import Card from "../../components/Card.vue";
import CardActionButton from "../../components/CardActionButton.vue";
import Chip from "../../components/Chip.vue";

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
    };
  },
  computed: {
    to() {
      return `/chime/${this.chime.id}/folder/${this.folder.id}`;
    },
  },
  methods: {
    handleDeleteClick() {
      if (!confirm("Are you sure you want to remove this folder?")) {
        return;
      }

      const chimeId = this.folder.chime_id;
      const folderId = this.folder.id;

      // optimistic UI: hide folder
      this.isFolderVisible = false;

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
.folder-card__contents {
  display: flex;
  flex-wrap: wrap;
  justify-content: space-between;
  align-items: baseline;
  gap: 0.25rem;
  margin-bottom: 1rem;
}
</style>
