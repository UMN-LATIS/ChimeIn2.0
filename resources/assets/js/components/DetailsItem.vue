<template>
  <div class="details-item">
    <span class="details-item-label" @click.stop="noop">
      <slot name="label" />
    </span>
    <span class="details-item-value" @click.stop="noop">
      <slot />
    </span>
  </div>
</template>
<script>
export default {
  methods: {
    noop() {},
  },
};
</script>
<style scoped>
.details-item {
  margin: 0.5rem 0;
  display: flex;
  align-items: baseline;
  gap: 0.5rem;
}
.details-item-label {
  font-weight: normal;
  text-transform: uppercase;
  color: #aaa;
  font-size: 0.7rem;
}
.details-item-value {
  font-size: 0.9rem;
  color: #777;
  user-select: all;
  overflow: hidden;
  text-overflow: ellipsis;
}
</style>

<style>
.details-item-value a {
  color: #777;
}
</style>
