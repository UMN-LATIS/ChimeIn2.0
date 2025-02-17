<template>
  <button
    class="btn btn-sm tw-flex tw-items-center tw-justify-center gap-1"
    type="button"
    :class="{
      'btn-outline-secondary': props.forceSyncState === 'error',
      'btn-success': props.forceSyncState !== 'error',
    }"
  >
    <slot />
    <span
      class="sync-status md-18 material-icons"
      :class="{
        'sync-status--is-success': props.forceSyncState === 'success',
        'sync-status--is-inprogress': props.forceSyncState === 'inProgress',
        'sync-status--is-error': props.forceSyncState === 'error',
        'sync-status--is-idle': props.forceSyncState === 'idle',
      }"
    >
      {{ currentStatusIcon.icon }}
    </span>
    <span class="sr-only">{{ currentStatusIcon.alt }}</span>
  </button>
</template>

<script setup lang="ts">
import { computed } from "vue";
import { AsyncActionState } from "@/types";

const props = defineProps<{
  forceSyncState: AsyncActionState;
}>();

const statuses: Record<AsyncActionState, { icon: string; alt: string }> = {
  success: {
    icon: "check_circle",
    alt: "Success",
  },
  inProgress: {
    icon: "sync",
    alt: "Sync In Progress... please wait",
  },
  error: {
    icon: "error",
    alt: "Error",
  },
  idle: {
    icon: "sync",
    alt: "Start Sync",
  },
};

const currentStatusIcon = computed(() => statuses[props.forceSyncState]);
</script>

<style scoped>
.sync-status {
  display: flex;
  align-items: center;
}

.sync-status--is-inprogress {
  animation: 1s infinite spin;
}

.sync-status--is-error {
  margin: 0.5rem 0;
  color: #dc3545;
  font-size: 0.9rem;
}

@keyframes spin {
  from {
    transform: rotate(360deg);
  }
  to {
    transform: rotate(0deg);
  }
}
</style>
