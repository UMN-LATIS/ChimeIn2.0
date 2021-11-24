<template>
  <div class="details-item">
    <span class="details-item-label">
      <slot name="label" />
    </span>
    <span class="details-item-value">
      <slot />
    </span>
  </div>
</template>
<style scoped>
.details-item {
  margin: 0.5rem 0;
  display: flex;
  align-items: baseline;
}
.details-item-label {
  width: 5rem;
  font-weight: normal;
  text-transform: uppercase;
  color: #aaa;
  font-size: 0.7rem;
  margin-right: 1rem;
}
.details-item-value {
  font-size: 0.9rem;
  color: #777;
}
</style>

<style>
.details-item-value a {
  color: #777;
}
</style>
