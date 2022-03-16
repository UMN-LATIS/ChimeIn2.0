<template>
  <div class="textarea-input form-group">
    <label
      :for="name"
      class="form-group__label"
      :class="{
        'sr-only': visuallyHideLabel,
      }"
      >{{ label }}</label
    >
    <textarea
      :id="name"
      v-bind="$attrs"
      class="form-control"
      :value="value"
      @input="$emit('input', $event.target.value)"
    />
  </div>
</template>
<script>
export default {
  props: {
    name: { type: String, required: true },
    label: { type: String, required: true },
    value: { type: String, default: "" },
    visuallyHideLabel: { type: Boolean, default: false },
  },
};
</script>

<style scoped>
.form-group__label {
  margin-bottom: 0.25rem;
}
</style>
