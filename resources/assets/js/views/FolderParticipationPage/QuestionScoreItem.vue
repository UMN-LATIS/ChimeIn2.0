<template>
  <div
    class="question-score-item d-flex align-items-center justify-content-center"
  >
    <Icon v-if="score === 1">star</Icon>
    <Icon v-else-if="score < 1 && valueForIncorrect > 0">star_half</Icon>
    <Icon v-else class="no-points">close</Icon>
  </div>
</template>
<script setup lang="ts">
import Icon from "../../components/Icon.vue";

defineProps<{
  score: number;
  valueForIncorrect: number;
}>();
</script>
<style scoped>
.question-score-item {
  color: var(--gold);
}
.no-points {
  color: red;
}
</style>
