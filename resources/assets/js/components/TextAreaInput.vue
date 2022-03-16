<template>
  <div class="textarea-input form-group">
    <label :for="name" class="form-group__label">{{ label }}</label>
    <div class="form-group__description">
      <slot name="description"></slot>
    </div>
    <textarea
      :id="name"
      type="text"
      class="form-control"
      placeholder="Describe your image"
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
    placeholder: { type: String, default: "" },
    type: { type: String, default: "text" },
    value: { type: String, default: "" },
  },
};
</script>

<style scoped>
.form-group__label {
  margin-bottom: 0.25rem;
}
.textarea-input {
  max-width: 30rem;
}

.form-group__description {
  font-size: 0.75rem;
  color: #777;
  margin-bottom: 0.5rem;
}
</style>
