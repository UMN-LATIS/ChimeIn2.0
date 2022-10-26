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

      <section>
        <h3>Score Settings</h3>
        <dl>
          <div v-if="LTIGradeMode">
            <dt>LTI Grade Mode</dt>
            <dd>{{ LTIGradeMode }}</dd>
          </div>

          <dt>Partial credit</dt>
          <dd>{{ PartialCreditSetting }}</dd>
        </dl>
      </section>

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
          </tr>
        </thead>
        <tbody>
          <UserParticipationRow
            v-for="participant in participants"
            :key="participant.id"
            :user="participant"
            :questions="folder?.questions ?? []"
            :responses="getResponsesForUser(participant.id, responses)"
            :numberOfActiveQuestions="numberOfActiveQuestions"
          />
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
  LTIGradeOptions,
} from "../../types";
import { onMounted, ref, computed } from "vue";
import * as api from "../../common/api";
import { RouterLink } from "vue-router";
import getResponsesForUser from "./getResponsesForUser";
import UserParticipationRow from "./UserParticipationRow.vue";
import { uniq } from "ramda";

const props = defineProps<{
  user: User;
  chimeId: number;
  folderId: number;
}>();

const participationSummary = ref<ChimeFolderParticipationSummary>();
const chime = ref<Chime | null>(null);
const folder = ref<FolderWithQuestions | null>(null);
const responses = computed(
  (): ChimeFolderParticipationResponseItem[] =>
    participationSummary.value?.responses ?? []
);
const participants = computed(
  (): User[] => participationSummary.value?.participants ?? []
);
const LTIGradeMode = computed((): string | null => {
  const gradeMode = chime.value?.lti_grade_mode;
  if (!gradeMode) return null;
  if (gradeMode === "no_grades") return "No grades recorded";
  if (gradeMode === "one_grade") return "One grade recorded";
  if (gradeMode === "multiple_grades") return "One grade per folder";
  return null;
});

const PartialCreditSetting = computed((): string | null => {
  const ltiSetting = chime.value?.only_correct_answers_lti;

  if (ltiSetting === LTIGradeOptions.FULL_CREDIT_FOR_PARITICIPATION) {
    return "100%";
  }

  if (ltiSetting === LTIGradeOptions.HALF_CREDIT_FOR_PARTICIPATION) {
    return "50%";
  }

  if (ltiSetting === LTIGradeOptions.ONLY_POINTS_FOR_CORRECT) {
    return "0%";
  }

  return null;
});

/** questions with at least one response */
const numberOfActiveQuestions = computed((): number => {
  const questionIds = responses.value.map((r) => r.question_id);
  return uniq(questionIds).length;
});

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
</script>
<style scoped></style>
