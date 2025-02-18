<template>
  <div class="tw-flex tw-flex-col tw-items-center tw-gap-1 tw-flex-wrap">
    <img :src="qrCodeSrc" alt="QR Code" class="tw-rounded" />
    <a
      :href="url"
      target="_blank"
      rel="noopener noreferrer"
      class="tw-text-xs tw-break-all"
    >
      {{ prettyURL }}
    </a>
  </div>
</template>
<script setup lang="ts">
import { computedAsync } from "@vueuse/core";
import { generateQRCode } from "@/helpers/qrCode";
import { computed } from "vue";

const props = defineProps<{
  url: string;
}>();

const qrCodeSrc = computedAsync(async () => {
  return generateQRCode(props.url);
}, null);

const prettyURL = computed(() => props.url.replace(/^https?:\/\//, ""));
</script>
<style scoped></style>
