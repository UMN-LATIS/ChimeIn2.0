<template>
  <DefaultLayout :user="user" class="postit-layout">
    <template #navbar-left>
      <slot name="navbar-left" />
    </template>

    <PostIt>
      <slot />
    </PostIt>
  </DefaultLayout>
</template>
<script setup lang="ts">
import DefaultLayout from "./DefaultLayout.vue";
import { PostIt } from "@umn-latis/cla-vue-template";
import type { User } from "../types";

withDefaults(
  defineProps<{
    user?: User | null;
  }>(),
  {
    user: null,
  }
);
</script>
