<template>
  <nav aria-label="breadcrumb" class="breadcrumb-wrap">
    <ol class="breadcrumb container-fluid p-4">
      <slot />
    </ol>
  </nav>
</template>
<script setup lang="ts"></script>
<style scoped>
.breadcrumb-wrap {
  border-top: 1px solid #ddd;
}
.breadcrumb {
  background: none;
  margin: 0;
}
</style>
