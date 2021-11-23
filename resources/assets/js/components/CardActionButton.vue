<template>
  <button class="icon-button" @click="handleClick">
    <i class="material-icons icon-button__icon">{{ icon }}</i>
    <div class="icon-button__label">
      <slot />
    </div>
  </button>
</template>
<script>
export default {
  props: {
    icon: {
      type: String,
      required: true,
    },
  },
  methods: {
    handleClick(event) {
      this.$emit("click", event);
    },
  },
};
</script>
<style scoped>
.icon-button {
  display: inline-flex;
  padding: 0.75rem;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  border: 0;
  background: transparent;
}
.icon-button__icon {
  display: block;
  padding-bottom: 0.25rem;
}
.icon-button__label {
  font-size: 0.75em;
}
</style>
