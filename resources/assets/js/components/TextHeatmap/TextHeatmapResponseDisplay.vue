<template>
  <div class="text-heatmap-response-display" v-html="heatmapText"></div>
</template>

<script>
export default {
  props: ["question"],
  computed: {
    heatmapText() {
      return this.question.question_info.question_responses.heatmap_text;
    },
  },
};
</script>

<style scoped>
.text-heatmap-response-display {
  padding-left: 1rem;
  border-left: 0.25rem solid #ddd;
  font-size: 0.8rem;
}
</style>
