<template>
  <img :src="qrCodeSrc" alt="QR Code" class="tw-rounded" />
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
