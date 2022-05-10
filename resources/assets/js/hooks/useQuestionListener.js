import { onMounted, onUnmounted, reactive, toRef, computed } from "vue";
import axios from "axios";

function getFolderWithQuestions({ chimeId, folderId }) {
  return axios
    .get(`/api/chime/${chimeId}/folder/${folderId}?include_questions=true`)
    .then((res) => {
      return {
        ...res.data,
        // sort the questions within the folder by their order
        questions: res.data.questions.sort((a, b) => a.order - b.order),
      };
    })
    .catch((err) => console.error(err));
}

export default function useQuestionListener({ chimeId, folderId }) {
  const state = reactive({
    chimeId,
    folderId,
    folder: null,
    questions: computed(() => state.folder?.questions ?? []),
    usersCount: 0,
    errorHandlers: [],
    updateHandlers: [],
  });

  function onError(fn) {
    state.errorHandlers.push(fn);

    const removeHandler = () =>
      (state.errorHandlers = state.errorHandlers.filter((f) => f !== fn));

    return removeHandler;
  }

  function onUpdate(fn) {
    state.updateHandlers.push(fn);

    const removeHandler = () =>
      (state.updateHandlers = state.updateHandlers.filter((f) => f !== fn));

    return removeHandler;
  }

  function onEchoStartSession(event) {
    const question = state.questions.find(
      (q) => q.id === event.session.question.id
    );

    if (!question) return;

    // flag the question as part of the current session
    question.current_session_id = event.session.id;
    question.sessions.push({
      ...event.session,
      responses: [],
    });
  }

  function onEchoEndSession(event) {
    const question = state.questions.find(
      (q) => q.id === event.session.question.id
    );

    if (!question) return;

    // flag the question as not part of the current session
    question.current_session_id = null;
  }

  function onEchoSubmitResponse(event) {
    const question = state.questions.find(
      (q) => q.id === event.session.question.id
    );

    if (!question) return;

    const sessionToUpdate = question.sessions.find(
      (s) => s.id === event.session.id
    );

    if (!sessionToUpdate) return;

    const responseToUpdate = toRef(
      sessionToUpdate.responses.find((r) => r.id === event.response.id)
    );

    // if the response is not found, add it to the session responses
    if (!responseToUpdate.value) {
      sessionToUpdate.responses.push(event.response);
      return;
    }
    // otherwise, update the response
    responseToUpdate.value = event.response;
  }

  async function refresh() {
    state.folder = await getFolderWithQuestions({
      chimeId: state.chimeId,
      folderId: state.folder,
    });
  }

  onMounted(async () => {
    state.folder = await getFolderWithQuestions({ chimeId, folderId });

    Echo.join(`session-status.${chimeId}`)
      .here((users) => (state.usersCount = users.length))
      .joining(() => (state.usersCount += 1))
      .leaving(() => (state.usersCount -= 1))
      .listen("StartSession", onEchoStartSession)
      .listen("EndSession", onEchoEndSession);

    Echo.private(`session-response.${chimeId}`).listen(
      "SubmitResponse",
      onEchoSubmitResponse
    );
  });

  onUnmounted(() => {
    Echo.leave(`session-status.${chimeId}`);
    Echo.leave(`session-response.${chimeId}`);
  });

  return {
    questions: state.questions,
    onError,
    onUpdate,
    refresh,
  };
}
