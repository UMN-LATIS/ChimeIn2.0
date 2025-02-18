<template>
  <div
    class="tw-flex tw-flex-col tw-items-center tw-justify-center tw-bg-black/5 tw-rounded"
  >
    <button
      class="tw-border-0 tw-bg-transparent tw-rounded tw-p-2 tw-flex tw-items-center tw-justify-center tw-gap-2 tw-w-full hover:tw-bg-black/10 tw-transition-all"
      @click="isOpen = !isOpen"
    >
      <slot name="trigger" />
    </button>
    <div v-if="isOpen" class="tw-p-2">
      <slot />
    </div>
  </div>
</template>
<script setup lang="ts">
import { ref } from "vue";

const props = withDefaults(
  defineProps<{
    initiallyOpen?: boolean;
  }>(),
  {
    initiallyOpen: false,
  }
);

const isOpen = ref(props.initiallyOpen);
</script>
<style scoped></style>
