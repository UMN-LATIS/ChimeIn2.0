<template>
  <div class="impersonation-banner tw-bg-pink-500 tw-text-pink-100">
    <div
      class="tw-max-w-[90rem] mx-auto tw-p-4 tw-flex tw-justify-between tw-items-center"
    >
      <div>
        <p class="tw-text-xs tw-uppercase tw-font-semibold tw-text-pink-300">
          Impersonation
        </p>
        <p class="tw-text-sm">
          {{ user?.name }}
        </p>
        <p class="tw-text-xs">{{ user?.email }}</p>
      </div>
      <div>
        <a
          href="/impersonate/stop"
          class="tw-text-xs tw-font-semibold tw-text-pink-300 hover:tw-text-pink-200"
        >
          Stop Impersonating
        </a>
      </div>
    </div>
  </div>
</template>
<script setup lang="ts">
import { CurrentUser } from "@/types";
defineProps<{
  user?: CurrentUser | null;
}>();
</script>
<style scoped>
p {
  margin: 0;
}
</style>
