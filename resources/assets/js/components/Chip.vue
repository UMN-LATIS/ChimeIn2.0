<template>
  <span class="chip" :class="classMap">
    <slot />
  </span>
</template>
<script>
export default {
  props: {
    color: {
      type: String,
    },
    solid: {
      type: Boolean,
      default: false,
    },
  },
  computed: {
    classMap() {
      const colorClasses = [
        "primary",
        "secondary",
        "green",
        "yellow",
        "red",
      ].reduce(
        (acc, colorName) => ({
          ...acc,
          // ex. `chip--primary` class if <Chip color="primary" />
          [`chip--${colorName}`]:
            this.color && this.color.toLowerCase() === colorName,
        }),
        {}
      );

      return {
        ...colorClasses,
        "chip--solid": this.solid,
      };
    },
  },
};
</script>
<style scoped>
.chip {
  --color: #333;
  display: inline-flex;
  align-items: center;
  color: var(--color);
  border: 1px solid var(--color);
  font-size: 0.75rem;
  line-height: 1em;
  padding: 0.5em;
  border-radius: 1rem;
}

.chip--green {
  --color: var(--green);
  --color-inverse: var(--white);
}
.chip--yellow {
  --color: var(--gold);
  --color-inverse: var(--black);
}

.chip--solid {
  background: var(--color);
  color: var(--color-inverse);
}
</style>
