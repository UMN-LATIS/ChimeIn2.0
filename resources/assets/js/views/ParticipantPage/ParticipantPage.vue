<template>
  <DefaultLayout :user="user">
    <template #navbar-left>
      <Back v-if="!isCanvasChime" :to="`/`">Back to Home</Back>
    </template>
    <div class="participant-page my-4">
      <ViewModeNotice
        v-if="inParticipantView"
        :canvasUrl="canvasCourseUrl"
        :joinUrl="joinUrl"
      />
      <ErrorDialog />
      <div v-if="error" class="alert alert-warning" role="alert">
        {{ error }}
      </div>
      <VueAnnouncer />
      <div class="container" role="none">
        <section class="card">
          <!-- nav tabs -->
          <div class="card-header tw-text-xs sm:tw-text-sm">
            <h2 class="visually-hidden">Chime Questions</h2>
            <ul class="nav nav-tabs card-header-tabs" role="tablist">
              <li class="nav-item" role="none">
                <button
                  id="open-questions-tab"
                  class="nav-link"
                  :class="{ active: activeTab === 'open-questions' }"
                  type="button"
                  role="tab"
                  aria-controls="open-questions"
                  aria-selected="true"
                  @click="setActiveTab('open-questions')"
                >
                  Open Questions
                  <Chip
                    v-if="filteredSession.length > 0"
                    :solid="true"
                    color="black"
                    :aria-label="`${openQuestionCount} open questions`"
                    >{{ openQuestionCount }}</Chip
                  >
                </button>
              </li>
              <li class="nav-item" role="none">
                <button
                  id="answered-questions-tab"
                  class="nav-link"
                  :class="{ active: activeTab === 'answered-questions' }"
                  type="button"
                  role="tab"
                  aria-controls="answered-questions"
                  aria-selected="false"
                  @click="setActiveTab('answered-questions')"
                >
                  Answered Questions
                </button>
              </li>
            </ul>
          </div>

          <div v-if="chime" class="tab-content">
            <Transition>
              <div
                v-if="activeTab === 'open-questions'"
                id="open-questions"
                class="tab-pane container"
                :class="{ active: activeTab === 'open-questions' }"
                aria-live="polite"
                role="tabpanel"
                aria-labelledby="open-questions-tab"
              >
                <div v-if="ltiLaunchWarning">
                  <h1 class="text-center">
                    Whoops! You Didn't Follow the Link in Canvas
                  </h1>
                  <p class="text-left">
                    This chime is linked to Canvas. To participate, join this
                    chime by clicking the assignment link in your Canvas course.
                  </p>

                  <a
                    v-if="canvasCourseUrl"
                    class="btn btn-primary"
                    :href="canvasCourseUrl"
                  >
                    Go to Canvas
                  </a>
                  <p class="mt-3">
                    <small class="text-muted">
                      If you believe this message is in error, you may use
                      <a href="#" @click.prevent="forceLoad = true"
                        >this link</a
                      >
                      to force the chime to load. You may not recieve credit for
                      your responses. Please contact your instructor or
                      <a href="mailto:help@umn.edu" class="text-muted"
                        >help@umn.edu</a
                      >.
                    </small>
                  </p>
                </div>
                <template v-else>
                  <article
                    v-if="filteredSession.length < 1"
                    key="none"
                    class="text-center"
                  >
                    <h3>No Open Questions</h3>
                  </article>
                  <TransitionGroup
                    v-if="filteredSession.length > 0"
                    name="fade"
                  >
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
            </Transition>
            <Transition>
              <div
                v-if="activeTab === 'answered-questions'"
                id="answered-questions"
                class="tab-pane container"
                :class="{ active: activeTab === 'answered-questions' }"
                role="tabpanel"
                aria-labelledby="answered-questions-tab"
              >
                <div v-if="responses.length < 1" class="text-center">
                  <h1>No Answered Questions</h1>
                </div>
                <Response
                  v-for="(response, i) in responses"
                  v-else
                  :key="i"
                  :chime="chime"
                  :response="response"
                />
              </div>
            </Transition>
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
        </section>
      </div>
    </div>
  </DefaultLayout>
</template>

<script setup lang="ts">
import echoClient from "@/common/echoClient";
import axiosClient from "@/common/axiosClient";
import { ref, computed, onMounted, onUnmounted } from "vue";
import updateList from "ramda/es/update";
import ErrorDialog from "../../components/ErrorDialog.vue";
import ParticipantPrompt from "./ParticipantPrompt.vue";
import Response from "./ParticipantResponse.vue";
import ViewModeNotice from "./ViewModeNotice.vue";
import {
  selectCanvasCourseUrl,
  selectJoinUrl,
  selectIsCanvasChime,
} from "../../helpers/chimeSelectors";
import { useStore } from "vuex";
import { useAnnouncer } from "@vue-a11y/announcer";
import { useRoute } from "vue-router";
import DefaultLayout from "../../layouts/DefaultLayout.vue";
import Back from "../../components/Back.vue";
import * as T from "@/types";
import Chip from "@/components/Chip.vue";

const props = withDefaults(
  defineProps<{
    user: T.User | null;
    chimeId: T.Chime["id"];
    folderId: T.Folder["id"];
  }>(),
  {
    user: null,
  }
);

const chime = ref<T.Chime | null>(null);
const sessions = ref<T.Session[]>([]);
const responses = ref<T.Response[]>([]);
const error = ref<string | null>(null);
const timeout = ref<ReturnType<typeof setTimeout> | null>(null);
const loadTime = ref<Date | null>(null);
const forceLoad = ref(false);
const store = useStore();
const announcer = useAnnouncer();
const route = useRoute();
const activeTab = ref("open-questions");

const canvasCourseUrl = computed(() =>
  chime.value ? selectCanvasCourseUrl(chime.value) : null
);

const joinUrl = computed(() =>
  chime.value ? selectJoinUrl(chime.value) : null
);

const isCanvasChime = computed(() =>
  chime.value ? selectIsCanvasChime(chime.value) : null
);

const inParticipantView = computed(() => {
  const viewMode = route?.query?.viewMode ?? false;

  if (!viewMode || typeof viewMode !== "string") {
    return false;
  }

  return viewMode.toLowerCase() === "participant";
});

const filteredSession = computed(() => {
  return props.folderId
    ? sessions.value.filter((s) => s.question.folder_id === props.folderId)
    : sessions.value;
});

const ltiLaunchWarning = computed(
  () =>
    !forceLoad.value &&
    !inParticipantView.value &&
    !window.lti_launch &&
    isCanvasChime.value
);

const openQuestionCount = computed(() => {
  return filteredSession.value.length;
});

function updateResponse(updatedResponse) {
  const responseIndex = responses.value.findIndex(
    (response) => response.id === updatedResponse.id
  );

  const isNewResponse = responseIndex === -1;

  responses.value = isNewResponse
    ? [updatedResponse, ...responses.value]
    : updateList(responseIndex, updatedResponse, responses.value);
}

function loadChime() {
  axiosClient
    .get(`/api/chime/${props.chimeId}/openQuestions`)
    .then((res) => {
      chime.value = res.data.chime;
      document.title = chime.value?.name ?? "Chime";
      sessions.value = res.data.sessions;
    })
    .catch((err) => {
      if (err.response.data.status == "AttemptAuth") {
        window.location.href =
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
      return axiosClient
        .get(`/api/chime/${props.chimeId}/responses`)
        .then((res) => {
          responses.value = res.data;
        });
    });
}

const pusher = (echoClient.connector as any).pusher;
if (pusher) {
  pusher.connection.bind('connected', () => {
    console.log("reconnect: participant");

    if (timeout.value) clearTimeout(timeout.value);

    if (!loadTime.value) {
      throw new Error("loadTime should be set before reconnect");
    }

    const hoursSinceLoad =
      (new Date().getTime() - loadTime.value.getTime()) / 1000 / 60 / 60;
    if (hoursSinceLoad >= 8) {
      echoClient.leave(`session-status.${props.chimeId}`);
      pusher.connection.unbind('connected');
      error.value = "Your session has expired.  Please refresh the page.";
      sessions.value = [];
      announcer.polite("Your session has expired. Please refresh the page.");
      return;
    }

    timeout.value = setTimeout(() => {
      loadChime();
    }, 500);
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
});

function setActiveTab(tabId) {
  activeTab.value = tabId;
}

onUnmounted(() => {
  echoClient.leave(`session-status.props.${props.chimeId}`);
  const pusher = (echoClient.connector as any).pusher;
  if (pusher) {
    pusher.connection.unbind('connected');
  }
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

.nav-link {
  width: 100%;
  /* make inactive tab button background consistent across browsers */
  background: rgb(239, 239, 239);
}

.v-enter-active {
  transition: opacity 0.5s ease;
}

.v-enter-from,
.v-leave-to {
  opacity: 0;
}

.v-enter-to,
.v-leave-from {
  opacity: 1;
}

.tab-pane {
  padding: 2rem 1rem;
}
</style>
