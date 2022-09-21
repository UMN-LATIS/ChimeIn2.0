<template>
  <nav class="breadcrumb-nav" aria-label="Breadcrumb Navigation">
    <ol class="breadcrumb-nav__list">
      <li v-for="(link, i) in links" :key="i" class="breadcrumb-nav__list-item">
        <component
          :is="link.to ? RouterLink : 'span'"
          :to="link.to"
          class="breadcrumb-nav__list-item-content"
        >
          {{ link.name }}
        </component>
      </li>
    </ol>
  </nav>
</template>
<script setup lang="ts">
import { RouterLink } from "vue-router";

interface NavLink {
  name: string;
  to?: string;
}

defineProps<{
  links: NavLink[];
}>();
</script>
<style scoped>
.breadcrumb-nav {
  text-transform: uppercase;
  font-size: 0.8rem;
  --gap-size: 0.5rem;
}

.breadcrumb-nav__list {
  list-style: none;
  display: flex;
  gap: var(--gap-size);
  padding: 0;
  flex-wrap: wrap;
}

.breadcrumb-nav__list-item {
  display: flex;
  align-items: center;
}

.breadcrumb-nav__list-item:not(:last-child):after {
  content: "/";
  display: block;
  margin-left: var(--gap-size);
}
</style>
