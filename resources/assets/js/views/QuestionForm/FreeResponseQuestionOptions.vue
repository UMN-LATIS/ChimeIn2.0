<template>
  <div class="tw-my-4">
    <button
      type="button"
      class="tw-cursor-pointer tw-text-xs tw-font-medium tw-tracking-wide tw-uppercase tw-select-none tw-bg-transparent tw-border-none tw-p-0 tw-flex tw-items-center tw-gap-1 tw-text-neutral-600"
      :aria-expanded="isOpen"
      aria-controls="free-response-more-options"
      @click="isOpen = !isOpen"
    >
      <span
        class="material-icons tw-text-base tw-transition-transform tw-duration-200"
        :class="isOpen ? 'tw-rotate-90' : ''"
        >chevron_right</span
      >
      More Options
    </button>
    <Transition name="slide">
      <div
        v-show="isOpen"
        id="free-response-more-options"
        class="tw-grid tw-grid-cols-[auto_1fr_1fr] tw-items-center tw-justify-items-start tw-gap-4 mt-2 tw-bg-neutral-100 tw-rounded-lg tw-p-2"
      >
        <div
          class="tw-col-span-full tw-grid tw-grid-cols-subgrid tw-items-center"
          role="group"
          aria-labelledby="display-type-label"
        >
          <div id="display-type-label" class="tw-text-sm tw-text-neutral-600">
            Response Type
          </div>
          <label for="display-default" class="type-option">
            <input
              id="display-default"
              type="radio"
              class="sr-only"
              name="display-type"
              value="default"
              :checked="normedQuestionOptions.displayType === 'default'"
              @change="
                $emit('update:question_responses', {
                  ...normedQuestionOptions,
                  displayType: 'default',
                })
              "
            />

            <IconText />
            Text (Default)
          </label>
          <label for="display-code" class="type-option">
            <input
              id="display-code"
              type="radio"
              name="display-type"
              value="code"
              class="sr-only"
              :checked="normedQuestionOptions.displayType === 'code'"
              @change="
                $emit('update:question_responses', {
                  ...normedQuestionOptions,
                  displayType: 'code',
                })
              "
            />
            <IconCode />
            Code
          </label>
        </div>
        <label for="hideWordcloud" class="tw-m-0 tw-text-sm tw-text-neutral-600"
          >Hide wordcloud</label
        >
        <input
          id="hideWordcloud"
          :checked="normedQuestionOptions.hideWordcloud"
          type="checkbox"
          class="tw-col-span-2"
          @change="
            $emit('update:question_responses', {
              ...normedQuestionOptions,
              hideWordcloud: !normedQuestionOptions.hideWordcloud,
            })
          "
        />
      </div>
    </Transition>
  </div>
</template>

<script setup lang="ts">
import IconText from "@/icons/IconText.vue";
import IconCode from "@/icons/IconCode.vue";
import {
  FreeResponseQuestionInfo,
  NormedFreeResponseQuestionOptions,
} from "@/types";
import { computed, onMounted, ref } from "vue";
import { toNormedFreeResponseQuestionOptions } from "../../helpers/toNormedFreeResponseQuestionOptions";

const props = defineProps<{
  /**
   * It's possible – likely even – that the passed question_responses prop will
   * be a value like `[]` (the default set by other question types) or some
   * other value that doesn't match the FreeResponseQuestionInfo type.
   *
   * What this means is that we need to check that the question_responses prop
   * is an object with the properties we expect.
   */
  // eslint-disable-next-line vue/prop-name-casing
  question_responses: FreeResponseQuestionInfo["question_responses"];
}>();

const emit = defineEmits<{
  (
    event: "update:question_responses",
    value: NormedFreeResponseQuestionOptions
  ): void;
}>();

const normedQuestionOptions = computed(() =>
  toNormedFreeResponseQuestionOptions(props.question_responses)
);

const hasNonDefaultOptions =
  normedQuestionOptions.value.displayType !== "default" ||
  normedQuestionOptions.value.hideWordcloud;

const isOpen = ref(hasNonDefaultOptions);

onMounted(() => {
  // initialize the question options in case they're set to `[]`
  // or some other value that doesn't match the expected type
  emit("update:question_responses", normedQuestionOptions.value);
});
</script>

<style scoped>
.type-option {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  border: 1px solid #ced4da;
  padding: 0.5rem;
  border-radius: 4px;
  font-size: 0.875rem;
  justify-content: center;
  margin: 0;
}

.type-option:has(input:checked) {
  background-color: #333;
  border-color: #333;
  color: white;
}

.slide-enter-active,
.slide-leave-active {
  transition: all 0.2s ease;
  overflow: hidden;
}

.slide-enter-from,
.slide-leave-to {
  opacity: 0;
  max-height: 0;
  margin-top: 0;
  padding-top: 0;
  padding-bottom: 0;
}

.slide-enter-to,
.slide-leave-from {
  opacity: 1;
  max-height: 200px;
}
</style>
