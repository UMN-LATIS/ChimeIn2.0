<template>
  <div class="present-page">
    <NavBar
      v-if="!folder?.student_view"
      title="Back to Folder"
      :user="user"
      :host="host"
      :link="{ name: 'folder', params: { chimeId, folderId } }"
    />
    <ErrorDialog />

    <Spinner v-if="!folder" />
    <div v-if="chime" class="container-fluid present-container">
      <Fullscreen ref="fullscreenRef" @change="isFullscreen = !isFullscreen">
        <PresentQuestion
          v-if="currentQuestion"
          :usersCount="usersCount"
          :question="currentQuestion"
          :chime="chime"
          :folder="folder"
          @nextQuestion="nextQuestion"
          @previousQuestion="previousQuestion"
          @sessionUpdated="refreshQuestions"
          @toggle="() => fullscreenRef.toggle()"
          @reload="refreshQuestions"
        />
      </Fullscreen>
    </div>
  </div>
</template>

<script setup>
import { onMounted, ref, computed } from "vue";
import { component as Fullscreen } from "vue-fullscreen";
// import { questionsListener } from "../../mixins/questionsListener";
import useQuestionListener from "../../hooks/useQuestionListener.js";
// import toHyphenatedCode from "../../helpers/toHyphenatedCode.js";
import ErrorDialog from "../../components/ErrorDialog.vue";
import NavBar from "../../components/NavBar.vue";
import PresentQuestion from "./PresentQuestion.vue";
import Spinner from "../../components/Spinner.vue";
import { useRouter } from "vue-router";
import { mathMod } from "ramda";

const props = defineProps({
  user: {
    type: Object,
    required: true,
  },
  chimeId: {
    type: Number,
    required: true,
  },
  folderId: {
    type: Number,
    required: true,
  },
  questionIndex: {
    type: Number,
    default: 0,
  },
});

const {
  folder,
  questions,
  refresh: refreshQuestions,
} = useQuestionListener({
  chimeId: props.chimeId,
  folderId: props.folderId,
});
const chime = ref(null);
const isFullscreen = ref(false);
const fullscreenRef = ref(null);

const currentQuestion = computed(() => {
  if (props.questionIndex >= questions.length) {
    console.error(
      `No question exists at index ${props.questionIndex} in ${questions}`
    );
    return null;
  }
  return questions[props.questionIndex];
});

const host = computed(() =>
  chime.value && chime.value.join_instructions ? window.location.host : null
);
// const hyphenatedCode = computed(() =>
//   chime.value && chime.value.join_instructions
//     ? toHyphenatedCode(chime.value.access_code)
//     : ""
// );

const router = useRouter();

function nextQuestion() {
  const nextQuestionIndex = mathMod(props.questionindex + 1, questions.length);

  router.replace({
    name: "present",
    params: {
      chimeId: props.chimeId,
      folderId: props.folderId,
      questionIndex: nextQuestionIndex,
    },
  });
}

function previousQuestion() {
  const prevQuestionIndex = mathMod(props.questionIndex - 1, questions.length);

  router.replace({
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
