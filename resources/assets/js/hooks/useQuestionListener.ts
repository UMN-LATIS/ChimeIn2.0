import { onMounted, onUnmounted, ref, computed } from "vue";
import { getFolderWithQuestions, getChime } from "../common/api";
import echoClient from "../common/echoClient.js";
import { Chime, FolderWithQuestions, Maybe, Question } from "../types";

export default function useQuestionListener({ chimeId, folderId }) {
  const usersCount = ref(0);
  const folder = ref<Maybe<FolderWithQuestions>>(null);
  const chime = ref<Maybe<Chime>>(null);
  const fetchError = ref<Maybe<Error>>(null);

  const questions = computed<Question[]>({
    get() {
      return folder.value?.questions ?? [];
    },
    set(questions: Question[]) {
      if (!folder.value) {
        throw new Error(
          "cannot set questions on folder when folder ref is null"
        );
      }
      folder.value.questions = questions;
    },
  });

  async function refresh() {
    fetchError.value = null;
    try {
      folder.value = await getFolderWithQuestions({
        chimeId,
        folderId,
      });
    } catch (error) {
      fetchError.value = error as Error;
    }
  }

  onMounted(async () => {
    try {
      [folder.value, chime.value] = await Promise.all([
        getFolderWithQuestions({ chimeId, folderId }),
        getChime(chimeId),
      ]);
    } catch (error) {
      fetchError.value = error as Error;
    }

    echoClient
      .join(`session-status.${chimeId}`)
      .here((users) => (usersCount.value = users.length))
      .joining(() => (usersCount.value += 1))
      .leaving(() => (usersCount.value -= 1))
      .listen("StartSession", function onEchoStartSession(event) {
        console.log("Start Session", { event });
        const question = questions.value.find(
          (q) => q.id === event.session.question.id
        );

        if (!question) return;

        // flag the question as part of the current session
        question.current_session_id = event.session.id;
        question.sessions.push({
          ...event.session,
          responses: [],
        });
      })
      .listen("EndSession", function onEchoEndSession(event) {
        console.log("End Session", { event });
        const question = questions.value.find(
          (q) => q.id === event.session.question_id
        );

        if (!question) return;

        // flag the question as not part of the current session
        question.current_session_id = null;
      });

    echoClient
      .private(`session-response.${chimeId}`)
      .listen("SubmitResponse", function onEchoSubmitResponse(event) {
        console.log("Submit Response", { event });
        const question = questions.value.find(
          (q) => q.id === event.session.question.id
        );

        if (!question) {
          console.error(
            `Could not find question with id ${event.session.question.id}`
          );
          return;
        }

        const session = question.sessions.find(
          (s) => s.id === event.session.id
        );

        if (!session) {
          console.error(
            `Could not find session ${event.session.id} for question ${event.session.question.id}`
          );
          return;
        }

        const responseIndexToUpdate = session.responses.findIndex(
          (r) => r.id === event.response.id
        );

        // if the response is not found, add it to the session responses
        if (responseIndexToUpdate === -1) {
          session.responses.push(event.response);
          return;
        }
        // otherwise, update the response
        session.responses[responseIndexToUpdate] = event.response;
      });
  });

  onUnmounted(() => {
    echoClient.leave(`session-status.${chimeId}`);
    echoClient.leave(`session-response.${chimeId}`);
  });

  return {
    chime,
    folder,
    questions,
    usersCount,
    refresh,
    fetchError,
  };
}
