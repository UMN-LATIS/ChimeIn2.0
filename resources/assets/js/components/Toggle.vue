<template>
  <div class="toggle">
    <input
      :id="name"
      class="toggle__input visually-hidden"
      type="checkbox"
      :checked="checked"
      :name="name"
      v-bind="$props"
      @change="handleInputChange($event)"
    />
    <label class="toggle__label" :for="name">
      <span class="toggle__label-text">
        <slot />
      </span>
    </label>
  </div>
</template>

<script>
export default {
  props: {
    checked: {
      type: Boolean,
      required: true,
    },
    name: {
      type: String,
      required: true,
    },
    color: {
      type: String,
      default: "green",
    },
  },
  emits: ["change"],
  methods: {
    handleInputChange($event) {
      this.$emit("change", $event);
    },
  },
};
</script>

<style scoped>
.toggle {
  --height: 1.33rem;
  --width: 2.5rem;
  --color: var(--black);
  --padding: 0.75rem;
  width: calc(var(--width) + 2 * var(--padding) + 2px);
  padding: 0.8rem;
  padding-bottom: 0.25rem;
}
.toggle__label {
  position: relative;
  padding-top: var(--height);
  display: block;
  width: 100%;
  text-align: center;
  cursor: pointer;
}

/* toggle outline */
.toggle__label::before {
  content: "";
  display: block;
  border: 1px solid var(--color);
  height: var(--height);
  width: var(--width);
  border-radius: calc(var(--height) / 2);
  position: absolute;
  top: 0;
  left: 0;
}

/* toggle circle */
.toggle__label::after {
  --circle-diameter: calc(var(--height) - 6px);
  content: "";
  display: block;
  width: var(--circle-diameter);
  height: var(--circle-diameter);
  border-radius: 50%;
  background: var(--color);
  position: absolute;
  top: 3px;
  left: 3px;
  transition: 0.2s;
}

.toggle__input:checked ~ .toggle__label::before,
.toggle__input:checked ~ .toggle__label::after,
.toggle__input:checked ~ .toggle__label .toggle__label-text {
  --color: var(--green);
}
.toggle__input:checked ~ .toggle__label::after {
  --translation: calc(100% + 4px);
  transform: translateX(var(--translation));
}

.toggle__label-text {
  display: block;
  font-size: 0.75rem;
  margin-top: 0.4rem;
  color: var(--color);
  text-align: center;
}
</style>
