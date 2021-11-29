<template>
  <Card
    class="folder-card"
    data-cy="folder-card"
    :icon="showMoveIcon ? 'drag_handle' : 'arrow_forward'"
    iconClass="handle"
  >
    <router-link :to="`/chime/${chime.id}/folder/${folder.id}`">
      <h2>
        {{ folder.name }}
      </h2>
    </router-link>
    <template #actions>
      <CardActionButton
        icon="edit"
        :to="`/chime/${chime.id}/folder/${folder.id}`"
      >
        Edit
      </CardActionButton>
      <CardActionButton
        icon="play_circle_outline"
        :to="`/chime/${chime.id}/folder/${folder.id}/present`"
      >
        Present
      </CardActionButton>
    </template>
  </Card>
</template>
<script>
import Card from "../../components/Card.vue";
import CardActionButton from "../../components/CardActionButton.vue";

export default {
  components: {
    Card,
    CardActionButton,
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
};
</script>

<style scoped>
.folder-card {
  align-items: center;
}
</style>
