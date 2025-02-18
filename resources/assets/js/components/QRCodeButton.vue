<template>
  <ToggleablePanel>
    <template #trigger>
      <IconQRCode class="tw-h-5 tw-w-5" aria-hidden="true" />
      QR Code
    </template>

    <QRCode :url="url" class="tw-w-full" />
    <a :href="url" target="_blank" rel="noopener noreferrer" class="tw-hidden">
      {{ prettyUrl }}
    </a>
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
