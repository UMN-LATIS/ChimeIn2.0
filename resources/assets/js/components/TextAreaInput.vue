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
      :value="modelValue"
      @input="$emit('update:modelValue', $event.target.value)"
    />
  </div>
</template>
<script>
export default {
  props: {
    name: { type: String, required: true },
    label: { type: String, required: true },
    modelValue: { type: String, default: "" },
    visuallyHideLabel: { type: Boolean, default: false },
  },
  emits: ["update:modelValue"],
};
</script>

<style scoped>
.form-group__label {
  margin-bottom: 0.25rem;
}
</style>
