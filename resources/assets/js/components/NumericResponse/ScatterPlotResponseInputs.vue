<template>
  <div class="d-flex gap-3">
    <NumInput
      id="numeric-x-input"
      :label="questionOptions.x_axis_label"
      :modelValue="responseInfo.x ?? 0"
      :disabled="disabled"
      @update:modelValue="
        $emit('update:responseInfo', { ...responseInfo, x: $event })
      "
    />
    <NumInput
      id="numeric-y-input"
      :label="questionOptions.y_axis_label"
      :modelValue="responseInfo.y ?? 0"
      :disabled="disabled"
      @update:modelValue="
        $emit('update:responseInfo', { ...responseInfo, y: $event })
      "
    />
  </div>
</template>
<script setup lang="ts">
import NumInput from "@/components/NumberInputGroup.vue";
import {
  NumericResponseResponseInfo,
  NormalizedNumericQuestionOptions,
} from "@/types";

defineProps<{
  questionOptions: NormalizedNumericQuestionOptions;
  disabled: boolean;
  responseInfo: NumericResponseResponseInfo;
}>();

defineEmits<{
  (eventName: "update:responseInfo", value: NumericResponseResponseInfo): void;
}>();
</script>
<style scoped></style>
