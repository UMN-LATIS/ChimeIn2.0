<template>
  <DefaultLayout :user="user">
    <template #navbar-left>
      <Back :to="`/chime/${chimeId}`">Back to Chime</Back>
    </template>
    <ErrorDialog />
    <div class="container-fluid">
      <header>
        <p v-if="chime">
          <RouterLink :to="`/chime/${chime?.id}`">
            {{ chime?.name }}
          </RouterLink>
        </p>
        <h2 v-if="folder">{{ folder?.name }}</h2>
      </header>

      <h3>Participation Summary</h3>

      <table class="table table-sm">
        <thead>
          <tr>
            <th scope="col">Participant</th>
            <th
              v-for="(question, index) in folder?.questions"
              :key="question.id"
            >
              Q{{ index + 1 }}
            </th>
            <th>Total</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          <tr
            v-for="participant in participationSummary?.participants"
            :key="participant.id"
          >
            <th scope="row">
              <span>{{ participant.name }}</span>
              <span>{{ participant.email }}</span>
            </th>
            <td v-for="question in folder?.questions" :key="question.id">
              {{
                getQuestionScoreForUser({
                  userId: participant.id,
                  questionId: question.id,
                  allResponses: participationSummary?.responses ?? [],
                })
              }}
            </td>
            <td>
              <!-- total folder score -->
              {{
                (
                  getTotalFolderScoreForUser({
                    userId: participant.id,
                    allQuestionIds: questionIds,
                    allResponses: participationSummary?.responses ?? [],
                  }) * 100
                ).toFixed(2)
              }}%
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </DefaultLayout>
</template>
<script setup lang="ts">
import DefaultLayout from "../../layouts/DefaultLayout.vue";
import ErrorDialog from "../../components/ErrorDialog.vue";
import Back from "../../components/Back.vue";
import {
  Chime,
  ChimeFolderParticipationSummary,
  User,
  FolderWithQuestions,
  ChimeFolderParticipationResponseItem,
} from "../../types";
import { onMounted, ref, computed } from "vue";
import * as api from "../../common/api";
import { RouterLink } from "vue-router";
import { sum, uniq } from "ramda";

const props = defineProps<{
  user: User;
  chimeId: number;
  folderId: number;
}>();

const participationSummary = ref<ChimeFolderParticipationSummary>();
const chime = ref<Chime | null>(null);
const folder = ref<FolderWithQuestions | null>(null);

onMounted(async () => {
  [participationSummary.value, chime.value, folder.value] = await Promise.all([
    api.getChimeFolderParticipation({
      chimeId: props.chimeId,
      folderId: props.folderId,
    }),
    api.getChime(props.chimeId),
    api.getFolderWithQuestions({
      chimeId: props.chimeId,
      folderId: props.folderId,
    }),
  ]);
});

/**
 * questions with at least one response
 */
const questionIds = computed((): number[] => {
  const responses = participationSummary.value?.responses;
  if (!responses) return [];
  return uniq(responses.map((r) => r.question_id));
});

/**
 * Calculates the score for given question responses.
 * If the responses have at least one correct answer, the value is 1.
 * Otherwise the value is `valueOfIncorrectResponse`, 0 by default.
 * @param questionResponses
 * @param valueOfIncorrectResponse
 */
function calculateQuestionScore(
  questionResponses: ChimeFolderParticipationResponseItem[],
  valueOfIncorrectResponse = 0
) {
  return questionResponses.reduce(
    (acc, response) =>
      response.is_correct
        ? Math.max(acc, 1)
        : Math.max(acc, valueOfIncorrectResponse),
    0
  );
}

function getQuestionScoreForUser({
  userId,
  questionId,
  allResponses,
}: {
  userId: number;
  questionId: number;
  allResponses: ChimeFolderParticipationResponseItem[];
}): number {
  const questionResponsesForUser = allResponses.filter(
    (response) =>
      response.user_id === userId && response.question_id === questionId
  );

  return calculateQuestionScore(questionResponsesForUser);
}

function getTotalFolderScoreForUser({
  userId,
  allQuestionIds,
  allResponses,
}: {
  userId: number;
  allQuestionIds: number[];
  allResponses: ChimeFolderParticipationResponseItem[];
}): number {
  const questionScores = allQuestionIds.map((questionId) =>
    getQuestionScoreForUser({ userId, questionId, allResponses })
  );
  return sum(questionScores) / allQuestionIds.length;
}
</script>
<style scoped></style>
