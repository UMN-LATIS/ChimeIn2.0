<template>
  <div class="chimein-picker">
    <h5>Sign in to ChimeIn</h5>
    <p class="text-muted">
      Sign in once per computer. Every ChimeIn widget you add after this
      remembers your choice.
    </p>
    <button class="btn btn-primary" :disabled="signingIn" @click="onSignIn">
      {{ signingIn ? "Signing in…" : "Sign in" }}
    </button>
    <p v-if="error" class="text-danger mt-2">{{ error }}</p>
  </div>
</template>

<script setup lang="ts">
import { ref } from "vue";
import { signIn } from "./lib/auth";

const emit = defineEmits<{ (e: "signed-in", token: string): void }>();

const signingIn = ref(false);
const error = ref<string | null>(null);

async function onSignIn() {
  signingIn.value = true;
  error.value = null;
  try {
    const stored = await signIn();
    emit("signed-in", stored.token);
  } catch (e) {
    error.value = (e as Error).message;
  } finally {
    signingIn.value = false;
  }
}
</script>
