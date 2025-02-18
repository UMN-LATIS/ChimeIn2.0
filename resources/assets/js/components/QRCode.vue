<template>
  <a :href="url" target="_blank" rel="noopener noreferrer">
    <img :src="qrCodeSrc" alt="QR Code" class="tw-rounded" />
  </a>
</template>
<script setup lang="ts">
import { computedAsync } from "@vueuse/core";
import { generateQRCode } from "@/helpers/qrCode";

const props = defineProps<{
  url: string;
}>();

const qrCodeSrc = computedAsync(async () => {
  return generateQRCode(props.url);
}, null);
</script>
<style scoped></style>
