<template>
  <div>
    <div class="form-check">
      <input
        id="hideWordcloud"
        v-model="hideWordcloud"
        class="form-check-input"
        type="checkbox"
        @change="emitChanges"
      />
      <label class="form-check-label" for="hideWordcloud">Hide wordcloud</label>
    </div>
  </div>
</template>

<script>
export default {
  // eslint-disable-next-line vue/prop-name-casing, vue/require-prop-types
  props: ["question_responses"],
  emits: ["update:question_responses"],
  data: function () {
    return {
      hideWordcloud: this.question_responses.hideWordcloud,
    };
  },

  methods: {
    emitChanges: function () {
      this.$emit("update:question_responses", {
        hideWordcloud: this.hideWordcloud,
      });
    },
  },
};
</script>
