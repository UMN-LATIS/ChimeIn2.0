<template>
  <li class="breadcrumb-item" :class="{ active }">
    <router-link v-if="to" :to="to">
      <slot />
    </router-link>
    <a v-if="href" :href="href">
      <slot />
    </a>
    <span v-if="!to && !href">
      <slot />
    </span>
  </li>
</template>
<script setup lang="ts">
import { Maybe } from "../types";

withDefaults(
  defineProps<{
    href?: Maybe<string>;
    to?: Maybe<string>;
    active?: boolean;
  }>(),
  {
    href: null,
    to: null,
    active: false,
  }
);
</script>
