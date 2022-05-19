<template>
  <div>
    <div v-if="responses.length > 0">
      <VWordCloud
        v-if="!hideWordcloud"
        :text="processWithNLP ? topics : concatenatedResponses"
        :filteredWords="filteredWords"
        @click:word="handleWordClick"
      />
      <p>
        <label>
          <input
            type="checkbox"
            :checked="processWithNLP"
            @click="processWithNLP = !processWithNLP"
          />

          <strong>Experimental</strong>
          Filter for just names, places, and organizations.
        </label>
      </p>
      <p>Click a word within the wordcloud to ignore it.</p>

      <div v-if="filteredWords.length > 0" class="page-section">
        <h2 class="filter-list__title">Words ignored in Wordcloud</h2>
        <ul class="filter-list">
          <li
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
          </li>
        </ul>
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
                  {{ question.anonymous ? "Anonymous" : response.user.name }}
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

    <div v-else>No Responses Yet!</div>
  </div>
</template>

<script setup lang="ts">
import { computed, watch, ref } from "vue";
import VWordCloud from "./VWordCloud.vue";
import type { Question, Response } from "../../types";
import nlp from "compromise";

interface Props {
  responses: Response[];
  question: Question;
}

const props = defineProps<Props>();
const hideWordcloud = computed(
  () => props.question.question_info.question_responses.hideWordcloud
);
const responsesByMostRecent = computed(() => [...props.responses].reverse());
const concatenatedResponses = computed(() =>
  props.responses.map((r) => r.response_info.text).join("\n")
);
const topics = computed(() => {
  const doc = nlp(concatenatedResponses.value);

  return doc.topics().out("array").join("\n");
});

const processWithNLP = ref(false);
const filteredWords = ref<string[]>([]);

function handleWordClick(word) {
  filteredWords.value.push(word);
  console.log({ filteredWords: filteredWords.value });
}

watch(
  () => props.responses,
  () => {
    console.log(props.responses);
  }
);
</script>

<style scope>
.page-section {
  margin: 2rem 0;
}
.filter-list__title {
  font-size: 1rem;
  text-transform: uppercase;
  letter-spacing: 5%;
  font-weight: bold;
}

.filter-list {
  margin: 0;
  padding: 0;
  list-style: none;
  display: flex;
  flex-wrap: wrap;
  gap: 0.25rem;
}
.filter-list__item {
  display: inline-flex;
  border-radius: 2rem;
  padding: 0.5rem;
  padding-left: 1rem;
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
  background: rgba(0, 0, 0, 0.1);
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
