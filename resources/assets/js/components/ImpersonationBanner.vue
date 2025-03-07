<template>
  <div class="impersonation-banner tw-bg-[#c42da8] tw-text-pink-50 tw-sticky tw-top-0 tw-z-10">
    <div
      class="tw-max-w-[90rem] mx-auto tw-p-4 tw-flex tw-justify-between tw-items-center"
    >
      <div>
        <p class="tw-text-sm tw-font-semibold">
          {{ user?.name }}
        </p>
        <p class="tw-text-xs tw-text-pink-200">{{ user?.email }}</p>
      </div>
      <div>
        <a
          href="/impersonate/stop"
          class="tw-border tw-border-pink-50 tw-text-pink-50 tw-px-4 py-2 tw-rounded-md hover:tw-no-underline hover:tw-bg-pink-50 hover:tw-text-pink-800 tw-text-sm sm:tw-text-base"
        >
          Leave Impersonation
        </a>
      </div>
    </div>
  </div>
</template>
<script setup lang="ts">
import { CurrentUser, User } from "@/types";
defineProps<{
  user?: CurrentUser | User | null;
}>();
</script>
<style scoped>
p {
  margin: 0;
}
</style>
