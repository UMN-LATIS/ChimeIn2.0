<template>
  <component
    :is="type"
    v-bind="{ to, href }"
    class="icon-button"
    @click.stop="handleClick"
  >
    <i class="material-icons icon-button__icon">{{ icon }}</i>
    <div class="icon-button__label">
      <slot />
    </div>
  </component>
</template>
<script>
export default {
  props: {
    icon: {
      type: String,
      required: true,
    },
    to: {
      type: String,
    },
    href: {
      type: String,
    },
  },
  computed: {
    type() {
      if (this.to) return "router-link";
      if (this.href) return "a";
      return "button";
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
  cursor: pointer;
}
.icon-button__icon {
  display: block;
  padding-bottom: 0.25rem;
}
.icon-button__label {
  font-size: 0.75em;
}
</style>
