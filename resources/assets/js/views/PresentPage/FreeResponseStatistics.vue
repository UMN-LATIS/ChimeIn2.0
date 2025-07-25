<template>
  <div>
    <div v-if="responses.length > 0">
      <VWordCloud
        v-if="!hideWordcloud"
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

          <strong>Experimental</strong> Use Natural Language Processing to group
          names, places, and other entities.
        </label>
      </p>

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

        <table class="table table-striped response-table">
          <thead>
            <tr>
              <th scope="col">User</th>
              <th scope="col">Response</th>
            </tr>
          </thead>
          <tbody>
            <TransitionGroup name="fade">
              <tr v-for="response in responsesByMostRecent" :key="response.id">
                <th scope="row">
                  {{ question.anonymous ? "Anonymous" : userLookup.get(response.user_id)?.name ?? "-" }}
                </th>
                <td>
                  <p>{{ response.response_info.text }}</p>
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
import { computed, ref } from "vue";
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
const hideWordcloud = computed(() => {
  const question_responses = props.question.question_info.question_responses;
  return !Array.isArray(question_responses) && question_responses.hideWordcloud;
});
const responsesByMostRecent = computed(() => [...props.responses].reverse());

const processWithNLP = ref(false);
const responseTexts = computed(() =>
  props.responses.map((r) => r.response_info.text)
);

const wordFreqLookup = computed<WordFrequencyLookup>(() => {
  return processWithNLP.value
    ? getWordFreqLookupNLP(responseTexts.value.join("\n"), filteredWords.value)
    : toWordFrequencyLookup(responseTexts.value, filteredWords.value);
});

function handleWordClick(word) {
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
}

input[type="checkbox"] {
  margin-right: 0.5rem;
}
</style>
