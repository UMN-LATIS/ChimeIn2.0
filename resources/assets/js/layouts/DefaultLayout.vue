<template>
  <div class="default-layout">
    <AppHeader />
    <slot> Sorry. Nothing to see here. </slot>
    <AppFooter />
  </div>
</template>
<script setup lang="ts">
import AppHeader from "../../cla-vue-template/src/components/AppHeader.vue";
import AppFooter from "../../cla-vue-template/src/components/AppFooter.vue";
</script>
