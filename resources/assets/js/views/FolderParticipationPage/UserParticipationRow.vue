<template>
  <tr>
    <th scope="row" class="align-middle">
      <div class="d-flex flex-column">
        <span>{{ user.sortableName }}</span>
        <small class="text-muted">{{ user.email }}</small>
      </div>
    </th>
    <td
      v-for="questionId in questionIds"
      :key="questionId"
      class="align-middle"
    >
      <QuestionScoreItem
        v-if="userHasResponseForQuestion(user.id, questionId, responses)"
        :score="
          getQuestionScoreForUser({
            userId: user.id,
            questionId,
            responses,
            valueForIncorrect,
          })
        "
        :valueForIncorrect="valueForIncorrect"
      />
      <span
        v-else
        class="question-score-item d-flex align-items-center justify-content-center"
        >-</span
      >
    </td>
    <td class="text-right text-monospace text-muted text-sm align-middle">
      <div v-if="numberOfActiveQuestions > 0" class="tw-flex tw-flex-col  tw-justify-center tw-gap-1">
        <div>
          <div class="tw-font-bold">{{ percentScore }}</div>
          <div class="tw-text-xs tw-text-neutral-400">{{ totalQuestionScores }}/{{ numberOfActiveQuestions }}</div>
        </div>
      </div>
      <span v-else>-</span>
    </td>
  </tr>
</template>
<script setup lang="ts">
import { computed } from "vue";
import { sum, uniq } from "ramda";
import {
  ChimeFolderParticipationResponseItem,
  Question,
  SortableUser,
} from "../../types";
import getQuestionScoreForUser from "./getQuestionScoreForUser";
import QuestionScoreItem from "./QuestionScoreItem.vue";

const props = defineProps<{
  user: SortableUser;
  questions: Question[];
  responses: ChimeFolderParticipationResponseItem[];
  numberOfActiveQuestions: number;
  valueForIncorrect: number;
}>();

const questionIds = computed((): number[] =>
  uniq(props.questions.map((q) => q.id))
);

function userHasResponseForQuestion(
  userId: number,
  questionId: number,
  responses: ChimeFolderParticipationResponseItem[]
) {
  return responses.some(
    (r) => r.user_id === userId && r.question_id === questionId
  );
}

const questionScores = computed((): number[] =>
  props.questions.map((question) =>
    getQuestionScoreForUser({
      userId: props.user.id,
      questionId: question.id,
      responses: props.responses,
      valueForIncorrect: props.valueForIncorrect,
    })
  )
);

const totalQuestionScores = computed((): number => sum(questionScores.value));

const percentScore = computed((): string => {
  if (props.numberOfActiveQuestions === 0) {
    return "-";
  }

  const percent =
    (totalQuestionScores.value / props.numberOfActiveQuestions) * 100;
  return `${Math.round(percent)}%`;
});
</script>
<style scoped>
th {
  position: sticky;
  left: 0;
  background: #f3f3f3;
}

th,
td {
  border-top: 0;
  border-bottom: 1px solid #ddd;
}
</style>
