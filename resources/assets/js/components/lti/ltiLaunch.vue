<template>
  <div class="lti-launch-page">
    <h1 class="lti-launch-page__heading">Canvas/ChimeIn Setup</h1>
    <LTIImportSelector
      :similar_chimes="ltiDetails.similar_chimes"
      v-if="ltiDetails.similar_chimes"
      v-on:new_chime="ltiDetails.similar_chimes = false"
    />
    <LTISetupPreferences v-else />
  </div>
</template>

<style scoped>
.lti-launch-page {
  max-width: 75rem;
  margin: auto;
}
.lti-launch-page__heading {
  margin-top: 4rem;
  font-weight: bold;
}
</style>

<script>
import LTIImportSelector from "./ltiImportSelector.vue";
import LTISetupPreferences from "./ltiSetupPreferences.vue";

export default {
  props: ["lti_details"],
  components: {
    LTIImportSelector,
    LTISetupPreferences,
  },
  data() {
    return {
      ltiDetails: this.lti_details,
    };
  },
};
</script>
