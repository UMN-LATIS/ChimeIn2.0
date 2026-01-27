<template>
  <div>
    <div v-if="responses.length > 0">
      <div class="tw-mb-2 tw-flex md:tw-justify-end md:-tw-mt-12">
        <button
          class="tw-bg-neutral-100 tw-border tw-border-neutral-300 tw-inline-block tw-rounded-md tw-px-3 tw-py-1 tw-text-sm hover:tw-bg-neutral-400"
          @click="isWordcloudHidden = !isWordcloudHidden"
        >
          {{ isWordcloudHidden ? "Show" : "Hide" }} Word Cloud
        </button>
      </div>
      <template v-if="!isWordcloudHidden">
        <VWordCloud
          :wordFreqLookup="wordFreqLookup"
          @click:word="handleWordClick"
        >
          <small class="m-0"> Click a word to filter it out. </small>
        </VWordCloud>
        <p>
          <label>
            <input
              type="checkbox"
              :checked="processWithNLP"
              @click="processWithNLP = !processWithNLP"
            />

            <strong>Experimental</strong> Use Natural Language Processing to
            group names, places, and other entities.
          </label>
        </p>
      </template>

      <div v-if="filteredWords.length > 0" class="filter-list">
        <h2 class="filter-list__title">Filtered Words</h2>
        <div
          v-for="(word, index) in filteredWords"
          :key="index"
          class="filter-list__item"
        >
          {{ word }}
          <button
            class="filter-list__remove-btn"
            @click="filteredWords.splice(index, 1)"
          >
            <i class="material-icons md-18 md-dark">close</i>
            <span class="sr-only">remove word from filter</span>
          </button>
        </div>
      </div>

      <section class="page-section">
        <h2 class="section-header">Responses</h2>

        <table class="table table-striped response-table !tw-max-w-[60rem]">
          <thead>
            <tr
              class="[&>th]:!tw-border-t-0 [&>th]:tw-p-1 [&>th]:!tw-align-baseline tw-text-xs tw-text-neutral-400"
            >
              <th scope="col" class="tw-w-36">User</th>
              <th scope="col">
                <div
                  class="tw-flex tw-gap-4 tw-justify-between tw-items-center"
                >
                  Response
                  <fieldset
                    class="tw-inline-flex tw-items-center tw-gap-1 tw-border tw-border-neutral-600 tw-rounded-md tw-p-0.5"
                  >
                    <legend class="tw-sr-only">Response Format</legend>
                    <label
                      v-for="format in allResponseFormats"
                      :key="format"
                      class="tw-inline-block tw-px-2 tw-py-1 tw-rounded tw-cursor-pointer text-xs mb-0 has-[:focus]:tw-ring-2 has-[:focus]:tw-ring-blue-500 relative"
                      :class="{
                        'tw-bg-neutral-900 tw-text-neutral-100':
                          format === responseFormat,
                      }"
                    >
                      <input
                        v-model="responseFormat"
                        type="radio"
                        :name="`text-format-${question.id}`"
                        :value="format"
                        class="tw-appearance-none tw-w-0 tw-h-0 tw-m-0 tw-p-0 tw-absolute"
                      />
                      {{ capitalize(format) }}
                    </label>
                  </fieldset>
                </div>
              </th>
            </tr>
          </thead>
          <tbody>
            <TransitionGroup name="fade">
              <tr v-for="response in responsesByMostRecent" :key="response.id">
                <th scope="row">
                  {{
                    question.anonymous
                      ? "Anonymous"
                      : userLookup.get(response.user_id)?.name ?? "-"
                  }}
                </th>
                <td>
                  <pre
                    v-if="responseFormat === 'monospace'"
                    class="tw-whitespace-pre-wrap tw-m-0"
                  ><code>{{ response.response_info.text }}</code></pre>
                  <p v-else class="tw-m-0">{{ response.response_info.text }}</p>
                </td>
              </tr>
            </TransitionGroup>
          </tbody>
        </table>
      </section>
    </div>

    <div v-else>No responses yet</div>
  </div>
</template>

<script setup lang="ts">
import { capitalize, computed, ref } from "vue";
import VWordCloud from "./VWordCloud.vue";
import toWordFrequencyLookup from "./toWordFrequencyLookup";
import type {
  FreeResponse,
  FreeResponseQuestion,
  User,
  WordFrequencyLookup,
} from "../../types";
import getWordFreqLookupNLP from "../../helpers/getWordFreqLookupNLP";

interface Props {
  responses: FreeResponse[];
  question: FreeResponseQuestion;
  userLookup: Map<User["id"], User>;
}

const props = defineProps<Props>();
const filteredWords = ref<string[]>([]);
const isHiddenByDefault = computed(
  () =>
    !Array.isArray(props.question.question_info.question_responses) &&
    props.question.question_info.question_responses.hideWordcloud
);

const isWordcloudHidden = ref(isHiddenByDefault.value);

const responsesByMostRecent = computed(() => [...props.responses].reverse());

const processWithNLP = ref(false);
const responseTexts = computed(() =>
  props.responses.map((r) => r.response_info.text)
);
type ResponseFormat = "default" | "monospace";
const responseFormat = ref<ResponseFormat>("default");
const allResponseFormats = ["default", "monospace"];

const wordFreqLookup = computed<WordFrequencyLookup>(() => {
  return processWithNLP.value
    ? getWordFreqLookupNLP(responseTexts.value.join("\n"), filteredWords.value)
    : toWordFrequencyLookup(responseTexts.value, filteredWords.value);
});

function handleWordClick(word: string) {
  filteredWords.value.push(word);
}
</script>

<style scope>
.page-section {
  margin: 2rem 0;
}
.filter-list {
  margin: 1rem 0;
  display: flex;
  flex-wrap: wrap;
  gap: 0.25rem;
  align-content: center;
}
.filter-list__title {
  font-size: 1rem;
  font-weight: bold;
  display: inline-flex;
  align-items: center;
  margin: 0;
}

.filter-list__item {
  display: inline-flex;
  border-radius: 2rem;
  padding: 0.25rem;
  padding-left: 0.75rem;
  cursor: pointer;
  background-color: #ddd;
  align-items: center;
  gap: 0.5rem;
}
.filter-list__remove-btn {
  background: none;
  border: none;
  display: flex;
  justify-content: center;
  align-items: center;
  background: rgba(255, 255, 255, 0.5);
  width: 1.5rem;
  height: 1.5rem;
  border-radius: 50%;
}

.response-table {
  max-width: 40rem;
  table-layout: fixed;
}

input[type="checkbox"] {
  margin-right: 0.5rem;
}
</style>
