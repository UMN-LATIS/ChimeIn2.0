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
              { name: 'Participation Report' },
            ]"
          >
          </BreadcrumbNav>
          <h2 v-if="folder">Participation Report</h2>
          <p>Participation for all questions within this folder.</p>
        </header>
      </div>

      <div v-if="participationSummary">
        <h3 class="text-base uppercase font-bold mt-3 mb-3">Participants</h3>
        <ScoreTable
          v-if="participants.length"
          :users="participants"
          :questions="questions"
          :responses="responses"
          :numberOfActiveQuestions="numberOfActiveQuestions"
          :valueForIncorrect="valueForIncorrect"
        />
        <span v-else>Nothing yet.</span>

        <h3 class="text-base uppercase font-bold mt-5 mb-3">Presenters</h3>
        <ScoreTable
          v-if="presenters.length"
          :users="presenters"
          :questions="questions"
          :responses="responses"
          :numberOfActiveQuestions="numberOfActiveQuestions"
          :valueForIncorrect="valueForIncorrect"
        />
        <span v-else>Nothing yet.</span>

        <section class="mt-5 mb-3">
          <h3 class="text-base uppercase font-bold">Details</h3>
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
      <div v-if="!participationSummary">
        <Spinner> Creating Report </Spinner>
      </div>
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
import BreadcrumbNav from "../../components/BreadcrumbNav.vue";
import ScoreTable from "./ScoreTable.vue";
import { uniq } from "ramda";
import { useStore } from "vuex";
import Spinner from "../../components/Spinner.vue";

const store = useStore();

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
const questions = computed(() => folder.value?.questions ?? []);

const LTIGradeMode = computed((): string | null => {
  const gradeMode = chime.value?.lti_grade_mode;
  if (!gradeMode) return null;
  if (gradeMode === "no_grades") return "No grades recorded";
  if (gradeMode === "one_grade") return "One grade recorded";
  if (gradeMode === "multiple_grades") return "One grade per folder";
  return null;
});

const valueForIncorrect = computed((): number => {
  const ltiSetting = chime.value?.only_correct_answers_lti;

  const lookup = {
    [LTIGradeOptions.FULL_CREDIT_FOR_PARITICIPATION]: 1,
    [LTIGradeOptions.HALF_CREDIT_FOR_PARTICIPATION]: 0.5,
    [LTIGradeOptions.ONLY_POINTS_FOR_CORRECT]: 0,
  };

  return lookup[ltiSetting ?? LTIGradeOptions.FULL_CREDIT_FOR_PARITICIPATION];
});

const PartialCreditSetting = computed(
  (): string => `${valueForIncorrect.value * 100}%`
);

/**
 * questions with at least one response from . For example, if the users are all participants,
 * responses for presenters don't count towards "active
 * questions"
 */
const numberOfActiveQuestions = computed((): number => {
  const participantIds = participants.value.map((p) => p.id);
  const questionIds = responses.value
    // only look at responses for this set of users
    .filter((r) => participantIds.includes(r.user_id))
    .map((r) => r.question_id);
  return uniq(questionIds).length;
});

onMounted(async () => {
  try {
    [participationSummary.value, chime.value, folder.value] = await Promise.all(
      [
        api.getChimeFolderParticipation({
          chimeId: props.chimeId,
          folderId: props.folderId,
        }),
        api.getChime(props.chimeId),
        api.getFolderWithQuestions({
          chimeId: props.chimeId,
          folderId: props.folderId,
        }),
      ]
    );
  } catch (err) {
    store.commit(
      "message",
      "Could not load folder scores. You may not have permission to view this page."
    );
    console.error(err);
  }
});
</script>
