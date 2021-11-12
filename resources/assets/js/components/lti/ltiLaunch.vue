<template>
  <div class="lti-launch-page">
    <h1 class="lti-launch-page__heading">Canvas/ChimeIn Setup</h1>
    <lti-import-selector
      :similar_chimes="ltiDetails.similar_chimes"
      v-if="ltiDetails.similar_chimes"
      v-on:new_chime="ltiDetails.similar_chimes = false"
    />
    <lti-setup-preferences v-else />
  </div>
</template>

<style scoped>
.lti-launch-page__heading {
  margin-top: 4rem;
  font-weight: bold;
}
</style>

<script>
export default {
  props: ["lti_details"],
  data() {
    return {
      ltiDetails: this.lti_details,
    };
  },
};
</script>
