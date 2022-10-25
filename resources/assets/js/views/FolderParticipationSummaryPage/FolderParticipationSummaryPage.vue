<template>
  <DefaultLayout :user="user">
    <template #navbar-left>
      <Back :to="`/chime/${chimeId}`">Back to Chime</Back>
    </template>
    <ErrorDialog />
    <div class="container-fluid">
      {{ chimeId }}
      {{ folderId }}
    </div>
  </DefaultLayout>
</template>
<script setup lang="ts">
import DefaultLayout from "../../layouts/DefaultLayout.vue";
import ErrorDialog from "../../components/ErrorDialog.vue";
import Back from "../../components/Back.vue";
import { User } from "../../types";
// import { onMounted } from "vue";

defineProps<{
  user: User;
  chimeId: number;
  folderId: number;
}>();
</script>
<style scoped></style>
