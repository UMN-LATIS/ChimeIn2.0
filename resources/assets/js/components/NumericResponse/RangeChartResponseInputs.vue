<template>
  <div>
    <p class="mb-0">
      {{ questionOptions.x_axis_label }}
    </p>
    <div class="d-flex gap-3">
      <NumInput
        id="range-min"
        label="Min"
        :modelValue="currentMin"
        :disabled="disabled"
        @update:modelValue="updateMin"
      />
      <NumInput
        id="range-max"
        label="Max"
        :modelValue="responseInfo?.xRange?.[1] ?? 0"
        :disabled="disabled"
        @update:modelValue="updateMax"
      />
    </div>
  </div>
</template>
<script setup lang="ts">
import NumInput from "@/components/NumberInputGroup.vue";
import {
  NumericResponseResponseInfo,
  NormalizedNumericQuestionOptions,
} from "@/types";
import { computed } from "vue";

const props = defineProps<{
  questionOptions: NormalizedNumericQuestionOptions;
  disabled: boolean;
  responseInfo: Pick<NumericResponseResponseInfo, "question_type" | "xRange">;
}>();

const emit = defineEmits<{
  (eventName: "update:responseInfo", value: NumericResponseResponseInfo): void;
}>();

const currentMin = computed(() => props.responseInfo.xRange?.[0] ?? 0);
const currentMax = computed(() => props.responseInfo.xRange?.[1] ?? 0);

function updateMin(value: number) {
  emit("update:responseInfo", {
    ...props.responseInfo,
    xRange: [value, currentMax.value],
  });
}

function updateMax(value: number) {
  emit("update:responseInfo", {
    ...props.responseInfo,
    xRange: [currentMin.value, value],
  });
}
</script>
<style scoped></style>
