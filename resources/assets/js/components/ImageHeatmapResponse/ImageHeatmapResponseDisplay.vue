<template>
  <img
    class="img-fluid max-height-image"
    :src="'/storage/' + question.question_info.question_responses.image"
  />
</template>

<script>
export default {
  props: ["question"],
};
</script>

<style>
.max-height-image {
  max-height: 70vh;
}
</style>
