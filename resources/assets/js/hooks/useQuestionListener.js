import { onMounted, onUnmounted, ref, computed } from "vue";
import { getFolderWithQuestions } from "../common/api.ts";

export default function useQuestionListener({ chimeId, folderId }) {
  const usersCount = ref(0);
  const folder = ref(null);
  const questions = computed({
    get() {
      return folder.value?.questions ?? [];
    },
    set(questions) {
      folder.value = {
        ...folder.value,
        questions,
      };
    },
  });

  async function refresh() {
    folder.value = await getFolderWithQuestions({
      chimeId,
      folderId,
    });
  }

  onMounted(async () => {
    folder.value = await getFolderWithQuestions({ chimeId, folderId });

    Echo.join(`session-status.${chimeId}`)
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

    Echo.private(`session-response.${chimeId}`).listen(
      "SubmitResponse",
      function onEchoSubmitResponse(event) {
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
      }
    );
  });

  onUnmounted(() => {
    Echo.leave(`session-status.${chimeId}`);
    Echo.leave(`session-response.${chimeId}`);
  });

  return {
    folder,
    questions,
    usersCount,
    refresh,
  };
}
