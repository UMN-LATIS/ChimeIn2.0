<template>
  <NumInput
    :id="`numeric-x-input`"
    :label="questionOptions.x_axis_label"
    :modelValue="responseInfo.x ?? 0"
    @update:modelValue="
      $emit('update:responseInfo', { ...responseInfo, x: $event })
    "
  />
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
