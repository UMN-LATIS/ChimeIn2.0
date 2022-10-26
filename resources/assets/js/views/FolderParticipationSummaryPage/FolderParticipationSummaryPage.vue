<template>
  <DefaultLayout :user="user">
    <template #navbar-left>
      <Back :to="`/chime/${chimeId}/folder/${folderId}`">Back to Folder</Back>
    </template>
    <ErrorDialog />
    <div class="container-fluid pt-4">
      <div class="d-flex justify-content-between align-items-center">
        <header>
          <BreadcrumbNav
            v-if="chime && folder"
            :links="[
              { name: 'Home', to: '/' },
              { name: chime.name, to: `/chime/${chime.id}` },
              {
                name: folder.name,
                to: `/chime/${chime.id}/folder/${folder.id}`,
              },
              { name: 'Participation Scores' },
            ]"
          >
          </BreadcrumbNav>
          <h2 v-if="folder">Folder Participation Scores</h2>
          <p>Summary of scores for all questions within this folder.</p>
        </header>
      </div>

      <!-- Participants Table -->
      <div class="table-responsive table-container">
        <table class="table">
          <thead>
            <tr class="text-xs uppercase">
              <th scope="col">Participant</th>
              <th
                v-for="(question, index) in folder?.questions"
                :key="question.id"
                scope="col"
                class="text-center"
              >
                Q{{ index + 1 }}
              </th>
              <th scope="col" class="text-right">Total</th>
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

      <!-- Presenters Table -->
      <h3 class="mt-5 mb-3">Presenters</h3>
      <div class="table-responsive table-container">
        <table class="table">
          <thead>
            <tr class="text-xs uppercase">
              <th scope="col">Presenter</th>
              <th
                v-for="(question, index) in folder?.questions"
                :key="question.id"
                scope="col"
                class="text-center"
              >
                Q{{ index + 1 }}
              </th>
              <th scope="col" class="text-right">Total</th>
            </tr>
          </thead>
          <tbody>
            <UserParticipationRow
              v-for="presenter in presenters"
              :key="presenter.id"
              :user="presenter"
              :questions="folder?.questions ?? []"
              :responses="getResponsesForUser(presenter.id, responses)"
              :numberOfActiveQuestions="numberOfActiveQuestions"
            />
          </tbody>
        </table>
      </div>

      <section class="max-w-fit mt-5 mb-3">
        <h3>Score Settings</h3>
        <div class="max-w-fit">
          <div class="card">
            <div class="card-body">
              <h4 class="h6 text-muted">Credit for Incorrect</h4>
              <p class="m-0">{{ PartialCreditSetting }}</p>
            </div>
          </div>

          <div v-if="LTIGradeMode" class="card">
            <div class="card-body">
              <h4 class="h6 text-muted d-flex align-items-center gap-2">
                Grade Mode <Chip color="yellow" :solid="true">Canvas</Chip>
              </h4>
              <small class="text-muted d-block">
                How grades are recorded in Canvas
              </small>
              <p>{{ LTIGradeMode }}</p>
            </div>
          </div>
        </div>
      </section>
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
import Chip from "../../components/Chip.vue";
import getResponsesForUser from "./getResponsesForUser";
import UserParticipationRow from "./UserParticipationRow.vue";
import { uniq } from "ramda";
import BreadcrumbNav from "../../components/BreadcrumbNav.vue";

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
const presenters = computed(
  (): User[] => participationSummary.value?.presenters ?? []
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
<style scoped>
.table td,
.table th {
  border-top: 0;
  border-bottom: 1px solid #dee2e6;
}

.table-container {
  border: 1px solid #ddd;
  background: #fff;
  padding: 2rem;
  width: fit-content;
  min-width: 40rem;
}
</style>
