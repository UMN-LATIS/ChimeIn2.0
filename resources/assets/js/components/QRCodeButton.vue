<template>
  <ToggleablePanel>
    <template #trigger>
      <IconQRCode class="tw-h-5 tw-w-5" aria-hidden="true" />
      QR Code
    </template>

    <QRCode :url="url" class="tw-w-full" />
    <p class="tw-text-center tw-text-xs tw-p-2 tw-m-0">
      <a :href="url" target="_blank" rel="noopener noreferrer">
        {{ prettyUrl }}
      </a>
    </p>
  </ToggleablePanel>
</template>
<script setup lang="ts">
import { computed } from "vue";
import { IconQRCode } from "@/icons";
import QRCode from "@/components/QRCode.vue";
import ToggleablePanel from "./ToggleablePanel.vue";

const props = defineProps<{
  url: string;
}>();

const prettyUrl = computed(() => props.url.replace(/https?:\/\//, ""));
</script>
<style scoped></style>
