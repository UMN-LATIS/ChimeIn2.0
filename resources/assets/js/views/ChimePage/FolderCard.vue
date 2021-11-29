<template>
  <Card
    class="folder-card"
    data-cy="folder-card"
    :icon="showMoveIcon ? 'drag_handle' : ''"
    iconClass="handle"
    @click.native="$router.push(to)"
  >
    <router-link :to="to">
      <h2>
        {{ folder.name }}
      </h2>
    </router-link>
    <template #actions>
      <CardActionButton icon="edit" :to="to"> Edit </CardActionButton>
      <CardActionButton icon="play_circle_outline" :to="`${to}/present`">
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
  computed: {
    to() {
      return `/chime/${this.chime.id}/folder/${this.folder.id}`;
    },
  },
};
</script>

<style scoped>
.folder-card {
  align-items: center;
}
</style>
