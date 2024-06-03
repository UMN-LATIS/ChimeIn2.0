<template>
  <DefaultLayout :user="user" class="bg-white">
    <template #navbar-left>
      <Back :to="`/chime/${chimeId}/folder/${folderId}`">Back to Folder</Back>
    </template>
    <div class="present-page">
      <ErrorDialog />

      <Spinner v-if="!folder" />
      <div v-if="folder && chime" class="present-container">
        <Fullscreen @change="isFullscreen = !isFullscreen">
          <PresentQuestion
            v-if="currentQuestion"
            :usersCount="usersCount"
            :question="currentQuestion"
            :chime="chime"
            :folder="folder"
            @nextQuestion="nextQuestion"
            @previousQuestion="previousQuestion"
            @sessionUpdated="refreshQuestions"
            @toggle="isFullscreen = !isFullscreen"
            @reload="refreshQuestions"
          />
        </Fullscreen>
      </div>
    </div>
  </DefaultLayout>
</template>

<script setup lang="ts">
import { onMounted, ref, computed } from "vue";
import { component as Fullscreen } from "vue-fullscreen";
import useQuestionListener from "../../hooks/useQuestionListener";
import ErrorDialog from "../../components/ErrorDialog.vue";
import DefaultLayout from "../../layouts/DefaultLayout.vue";
import PresentQuestion from "./PresentQuestion.vue";
import Spinner from "../../components/Spinner.vue";
import { useRouter } from "vue-router";
import { mathMod } from "ramda";
import Back from "../../components/Back.vue";
import * as T from "@/types";
import axios from "@/common/axiosClient";

const props = withDefaults(
  defineProps<{
    user: T.User;
    chimeId: number;
    folderId: number;
    questionIndex?: number;
  }>(),
  {
    questionIndex: 0,
  }
);

const {
  chime,
  folder,
  questions,
  usersCount,
  refresh: refreshQuestions,
} = useQuestionListener({
  chimeId: props.chimeId,
  folderId: props.folderId,
});

const isFullscreen = ref(false);

const currentQuestion = computed(() => {
  if (props.questionIndex >= questions.value.length) {
    console.error(
      `No question exists at index ${props.questionIndex} in ${questions}`
    );
    return null;
  }
  return questions.value[props.questionIndex];
});

const router = useRouter();

function nextQuestion() {
  const nextQuestionIndex = mathMod(
    props.questionIndex + 1,
    questions.value.length
  );

  router.push({
    name: "present",
    params: {
      chimeId: props.chimeId,
      folderId: props.folderId,
      questionIndex: nextQuestionIndex,
    },
  });
}

function previousQuestion() {
  const prevQuestionIndex = mathMod(
    props.questionIndex - 1,
    questions.value.length
  );

  router.push({
    name: "present",
    params: {
      chimeId: props.chimeId,
      folderId: props.folderId,
      questionIndex: prevQuestionIndex,
    },
  });
}

onMounted(() => {
  axios
    .get(`/api/chime/${props.chimeId}`)
    .then((res) => {
      chime.value = res.data;
    })
    .catch((err) => {
      console.error(`cannot get chime ${props.chimeId}`, err);
    });
});
</script>

<style>
.fullscreen {
  background: #fff;
  overflow-y: auto;
  padding-bottom: 6rem;
  overflow-x: hidden;
}
</style>
