<template>
  <tr>
    <th scope="row" class="align-middle">
      <div class="d-flex flex-column">
        <span>{{ user.name }}</span>
        <small class="text-muted">{{ user.email }}</small>
      </div>
    </th>
    <td
      v-for="questionId in questionIds"
      :key="questionId"
      class="align-middle"
    >
      <QuestionScoreItem
        :score="getQuestionScoreForUser({ questionId, responses })"
      />
    </td>
    <td class="text-right text-monospace text-muted text-sm align-middle">
      {{ total }}
    </td>
  </tr>
</template>
<script setup lang="ts">
import { computed } from "vue";
import { sum, uniq } from "ramda";
import {
  ChimeFolderParticipationResponseItem,
  Question,
  User,
} from "../../types";
import getQuestionScoreForUser from "./getQuestionScoreForUser";
import QuestionScoreItem from "./QuestionScoreItem.vue";

const props = defineProps<{
  user: User;
  questions: Question[];
  /** responses from this user for this folder */
  responses: ChimeFolderParticipationResponseItem[];
  numberOfActiveQuestions: number;
}>();

const questionIds = computed((): number[] =>
  uniq(props.questions.map((q) => q.id))
);

const total = computed((): string => {
  const questionScores: number[] = props.questions.map((question) =>
    getQuestionScoreForUser({
      questionId: question.id,
      responses: props.responses,
    })
  );

  const percent = (sum(questionScores) / props.numberOfActiveQuestions) * 100;
  return `${percent.toFixed(2)}%`;
});
</script>
<style scoped></style>
