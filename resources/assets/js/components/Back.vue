<template>
  <NavbarItem class="navbar-item--back">
    <router-link :to="to">
      <span class="material-icons">chevron_left</span>
      <slot>Back to Home</slot>
    </router-link>
  </NavbarItem>
</template>
<script setup lang="ts">
import { NavbarItem } from "@umn-latis/cla-vue-template";

withDefaults(
  defineProps<{
    to?: string;
  }>(),
  {
    to: "/",
  }
);
</script>
<style scoped></style>
