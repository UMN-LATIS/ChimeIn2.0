<template>
  <div class="chimein-picker">
    <h5>Choose a chime</h5>
    <p v-if="loading">Loading your chimes&hellip;</p>
    <p v-else-if="error" class="text-danger">{{ error }}</p>
    <ul v-else class="list-group">
      <li
        v-for="chime in chimes"
        :key="chime.id"
        class="list-group-item list-group-item-action"
        role="button"
        @click="$emit('chosen', chime)"
      >
        {{ chime.name }}
      </li>
      <li v-if="chimes.length === 0" class="list-group-item text-muted">
        You aren't a presenter for any chime yet.
      </li>
    </ul>
  </div>
</template>

<script setup lang="ts">
import { onMounted, ref } from "vue";
import { listChimes, OfficeChime } from "./lib/api";

const props = defineProps<{ browseToken: string }>();
defineEmits<{ (e: "chosen", chime: OfficeChime): void }>();

const chimes = ref<OfficeChime[]>([]);
const loading = ref(true);
const error = ref<string | null>(null);

onMounted(async () => {
  try {
    chimes.value = await listChimes(props.browseToken);
  } catch (e) {
    error.value = "Couldn't load your chimes. Try signing in again.";
  } finally {
    loading.value = false;
  }
});
</script>
