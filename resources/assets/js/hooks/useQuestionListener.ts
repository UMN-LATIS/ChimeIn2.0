import { onMounted, onUnmounted, ref, computed } from "vue";
import { getFolderWithQuestions, getChime, getChimeUsers, getChimeChannel } from "../common/api";
import echoClient from "../common/echoClient.js";
import * as T from "@/types";

export default function useQuestionListener({ chimeId, folderId }) {
  const usersCount = ref(0);
  const folder = ref<T.Maybe<T.FolderWithQuestions>>(null);
  const chime = ref<T.Maybe<T.Chime>>(null);
  const fetchError = ref<T.Maybe<Error>>(null);
  const userLookup = ref<Map<T.User["id"], T.User>>(new Map());

  const questions = computed<T.Question[]>({
    get() {
      return folder.value?.questions ?? [];
    },
    set(questions: T.Question[]) {
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
      let users;
      [folder.value, users] = await Promise.all([
        getFolderWithQuestions({
          chimeId,
          folderId,
        }),
        getChimeUsers(chimeId),
      ]);

      // reinitialize the user lookup map
      userLookup.value = new Map(users.map((u) => [u.id, u]));
    } catch (error) {
      fetchError.value = error as Error;
    }
  }

  let resyncUserCountTimeoutId = null as ReturnType<typeof setTimeout> | null;
  async function resyncSessionUserCount(interval = 10 * 60 * 1000) {
    // Clear any existing timeout to prevent multiple timers
    if (resyncUserCountTimeoutId) {
      clearTimeout(resyncUserCountTimeoutId);
    }

    resyncUserCountTimeoutId = setTimeout(async () => {
      const {
        user_count
      } = await getChimeChannel(chimeId, "session-status");

      usersCount.value = user_count;

      resyncSessionUserCount(interval);
    }, interval);
  }

  onMounted(async () => {
    try {
      let users;
      [folder.value, chime.value, users] = await Promise.all([
        getFolderWithQuestions({ chimeId, folderId }),
        getChime(chimeId),
        getChimeUsers(chimeId),
      ]);

      // reinitialize the user lookup map
      userLookup.value = new Map(users.map((u) => [u.id, u]));
    } catch (error) {
      fetchError.value = error as Error;
    }

    echoClient
      .join(`session-status.${chimeId}`)
      .here((users) => (usersCount.value = users.length))
      .joining(() => {
        usersCount.value += 1;
      })
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
      .listen(
        "SubmitResponse",
        function onEchoSubmitResponse(event: T.SubmitResponseEvent) {
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

          // add the user to the user lookup
          userLookup.value.set(event.response.user.id, event.response.user);

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

      // resync user count every 10 minutes
      resyncSessionUserCount(10 * 60 * 1000);
  });

  onUnmounted(() => {
    echoClient.leave(`session-status.${chimeId}`);
    echoClient.leave(`session-response.${chimeId}`);
    if (resyncUserCountTimeoutId) {
      clearTimeout(resyncUserCountTimeoutId);
    }
  });

  return {
    chime,
    folder,
    questions,
    usersCount,
    refresh,
    fetchError,
    userLookup,
  };
}
