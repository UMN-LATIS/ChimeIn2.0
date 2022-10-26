<template>
  <div class="question-score-item">
    <Icon v-if="score === 1">star</Icon>
    <Icon v-else-if="0 < score && score < 1">star_half</Icon>
    <span v-else>-</span>
  </div>
</template>
<script setup lang="ts">
import Icon from "../../components/Icon.vue";

defineProps<{
  score: number;
}>();
</script>
<style scoped></style>
