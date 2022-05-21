<template>
  <div class="participant-page">
    <ViewModeNotice
      v-if="inParticipantView"
      :canvasUrl="canvasCourseUrl"
      :joinUrl="joinUrl"
    />
    <NavBar :title="isCanvasChime ? null : 'Home'" :user="user" link="/" />
    <ErrorDialog />
    <div v-if="error" class="alert alert-warning" role="alert">
      {{ error }}
    </div>
    <VueAnnouncer />
    <main class="participant-page__main container">
      <div class="card">
        <!-- nav tabs -->
        <div class="card-header">
          <ul class="nav nav-tabs card-header-tabs">
            <li class="nav-item">
              <a
                class="nav-link active"
                data-toggle="tab"
                href="#currentQuestions"
                >Open Questions</a
              >
            </li>
            <li class="nav-item">
              <a class="nav-link" data-toggle="tab" href="#pastQuestions"
                >Answered Questions</a
              >
            </li>
          </ul>
        </div>

        <div class="tab-content">
          <!-- openQuestions -->
          <div
            id="currentQuestions"
            class="tab-pane container active"
            aria-live="polite"
          >
            <div v-if="ltiLaunchWarning">
              <h1 class="text-center">
                Whoops! You Didn't Follow the Link in Canvas
              </h1>
              <p class="text-left">
                This chime is linked to Canvas. To participate, join this chime
                by clicking the assignment link in your Canvas course.
              </p>

              <a class="btn btn-primary" :href="canvasCourseUrl">
                Go to Canvas
              </a>
              <p class="mt-3">
                <small class="text-muted">
                  If you believe this message is in error, you may use
                  <a href="#" @click.prevent="forceLoad = true">this link</a>
                  to force the chime to load. You may not recieve credit for
                  your responses. Please contact your instructor or
                  <a href="mailto:help@umn.edu" class="text-muted"
                    >help@umn.edu</a
                  >.
                </small>
              </p>
            </div>
            <template v-else>
              <div
                v-if="filteredSession.length < 1"
                key="none"
                class="text-center"
              >
                <h1>No Open Questions</h1>
              </div>
              <TransitionGroup v-if="filteredSession.length > 0" name="fade">
                <ParticipantPrompt
                  v-for="s in filteredSession"
                  :key="s.id"
                  :session="s"
                  :chime="chime"
                  :responses="responses"
                  @updateResponse="updateResponse"
                />
              </TransitionGroup>
            </template>
          </div>

          <!-- answered Questions -->
          <div id="pastQuestions" class="tab-pane container">
            <div v-if="responses.length < 1" class="text-center">
              <h1>No Answered Questions</h1>
            </div>
            <Response
              v-for="(response, i) in sortedResponses"
              v-else
              :key="i"
              :chime="chime"
              :response="response"
            />
          </div>
          <p v-if="!ltiLaunchWarning" class="text-center m-0">
            <small v-if="chime.lti_course_title" class="text-muted"
              >Not seeing the prompts you're looking for? Make sure you've
              followed the correct assignment link from Canvas.
            </small>
            <small
              v-if="
                chime.lti_course_title && chime.lti_grade_mode != 'no_grades'
              "
              class="text-muted"
              >Grades will generally be updated in Canvas within 4 hours.
            </small>
          </p>
        </div>
      </div>
    </main>
  </div>
</template>

<script setup>
import echoClient from "../../common/echoClient";
import { ref, computed, onMounted, onUnmounted } from "vue";
import updateList from "ramda/es/update.js";
import ErrorDialog from "../../components/ErrorDialog.vue";
import NavBar from "../../components/NavBar.vue";
import ParticipantPrompt from "./ParticipantPrompt.vue";
import Response from "./ParticipantResponse.vue";
import ViewModeNotice from "./ViewModeNotice.vue";
import {
  selectCanvasCourseUrl,
  selectJoinUrl,
  selectIsCanvasChime,
} from "../../helpers/chimeSelectors.js";
import { useStore } from "vuex";
import { useAnnouncer } from "@vue-a11y/announcer";
import { useRoute } from "vue-router";

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
});

const chime = ref({});
const sessions = ref([]);
const responses = ref([]);
const error = ref(null);
const timeout = ref(null);
const loadTime = ref(null);
const forceLoad = ref(false);
const store = useStore();
const announcer = useAnnouncer();
const route = useRoute();

const canvasCourseUrl = computed(() => selectCanvasCourseUrl(chime.value));
const joinUrl = computed(() => selectJoinUrl(chime.value));
const isCanvasChime = computed(() => selectIsCanvasChime(chime.value));
const inParticipantView = computed(() => {
  // const viewMode = get(this, "$route.query.viewMode", null);
  const viewMode = route?.query?.viewMode ?? false;
  if (!viewMode) return false;

  return viewMode.toLowerCase() === "participant";
});
const filteredSession = computed(() => {
  return props.folderId
    ? sessions.value.filter((s) => s.question.folder_id === props.folderId)
    : sessions.value;
});

const compareBy = (prop) => (a, b) => {
  if (a[prop] > b[prop]) return -1;
  if (a[prop] < b[prop]) return 1;
  return 0;
};
const sortedResponses = computed(() =>
  [...responses.value].sort(compareBy("updated_at"))
);

const ltiLaunchWarning = computed(
  () =>
    !forceLoad.value &&
    !inParticipantView.value &&
    !window.lti_launch &&
    isCanvasChime.value
);

function updateResponse(updatedResponse) {
  const responseIndex = responses.value.findIndex(
    (response) => response.id === updatedResponse.id
  );

  const isNewResponse = responseIndex === -1;

  responses.value = isNewResponse
    ? responses.value.concat(updatedResponse)
    : updateList(responseIndex, updatedResponse, responses.value);
}

function loadChime() {
  axios
    .get(`/api/chime/${props.chimeId}/openQuestions`)
    .then((res) => {
      chime.value = res.data.chime;
      document.title = chime.value.name;
      sessions.value = res.data.sessions.reverse();
    })
    .catch((err) => {
      if (err.response.data.status == "AttemptAuth") {
        window.location =
          "/loginAndRedirect?target=" + window.location.pathname;
      } else {
        if (err.response.data.message) {
          error.value = err.response.data.message;
        }
        store.commit(
          "message",
          "Could not load Chime. You may not have permission to view this page. "
        );
        console.error("error getting chime:", err);
      }
    })
    .then(() => {
      // hmmm... why not load responses in parallel with chime?
      return axios.get(`/api/chime/${props.chimeId}/responses`).then((res) => {
        responses.value = res.data;
      });
    });
}

onMounted(() => {
  loadChime();
  loadTime.value = new Date();
  echoClient
    .join(`session-status.${props.chimeId}`)
    .listen("StartSession", (event) => {
      console.log("StartSession: participant", { event });
      sessions.value.unshift(event.session);
      announcer.polite(
        "A new question has been open.  There are " +
          sessions.value.length +
          " questions open"
      );
    })
    .listen("EndSession", (event) => {
      console.log("EndSession: participant", { event });

      const removeIndex = sessions.value.findIndex(
        (s) => s.id == event.session.id
      );
      sessions.value.splice(removeIndex, 1);
      announcer.polite(
        "A question has been closed.  There are " +
          sessions.value.length +
          " questions open"
      );
    });

  echoClient.connector.socket.on("reconnect", (event) => {
    console.log("reconnect: participant", { event });

    if (timeout.value) clearTimeout(timeout.value);

    const hoursSinceLoad = (new Date() - loadTime.value) / 1000 / 60 / 60;
    if (hoursSinceLoad >= 8) {
      echoClient.leave(`session-status.${props.chimeId}`);
      echoClient.connector.socket.off("reconnect");
      error.value = "Your session has expired.  Please refresh the page.";
      sessions.value = [];
      announcer.polite("Your session has expired. Please refresh the page.");
      return;
    }

    timeout.value = setTimeout(() => {
      loadChime();
    }, 500);
  });
});

onUnmounted(() => {
  echoClient.leave(`session-status.props.${props.chimeId}`);
  echoClient.connector.socket.off("reconnect");
});
</script>
<style scoped>
@media (max-width: 30rem) {
  .participant-page__main {
    padding: 0;
  }
}

.nav-tabs {
  display: grid;
  grid-template-columns: 1fr 1fr;
  align-items: flex-end;
  text-align: center;
}

.tab-pane {
  padding: 2rem 1rem;
}
</style>
