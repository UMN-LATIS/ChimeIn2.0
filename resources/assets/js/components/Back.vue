<template>
  <NavbarItem class="navbar-item--back">
    <router-link :to="to">
      <span class="material-icons">chevron_left</span>
      <slot>Back to Home</slot>
    </router-link>
  </NavbarItem>
</template>
<script setup lang="ts">
import NavbarItem from "../../cla-vue-template/src/components/NavbarItem.vue";

withDefaults(
  defineProps<{
    to?: string;
  }>(),
  {
    to: "/",
  }
);
</script>
<style scoped>
@media (min-width: 1000px) {
  /* align back button content, not the back button edge */
  .navbar-item--back {
    transform: translateX(-1rem);
  }
}
</style>
