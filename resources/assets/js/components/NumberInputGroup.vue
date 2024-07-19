<template>
  <div class="number-input-group">
    <label :for="id">
      {{ label }}
    </label>
    <input
      :id="id"
      :value="modelValue"
      type="number"
      :disabled="disabled"
      class="form-control"
      @input="handleInput"
    />
  </div>
</template>
<script setup lang="ts">
withDefaults(
  defineProps<{
    id: string;
    label: string;
    modelValue: number;
    disabled?: boolean;
  }>(),
  {
    disabled: false,
  }
);

const emit = defineEmits<{
  (eventName: "update:modelValue", value: number): void;
}>();

function handleInput(event: Event) {
  const target = event.target as HTMLInputElement;

  const x = parseFloat(target.value);

  // ignore updates that are not numbers
  if (isNaN(x)) {
    return;
  }

  emit("update:modelValue", x);
}
</script>
<style scoped></style>
