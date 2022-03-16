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
    <vue-announcer />

    <!-- nav tabs -->
    <main class="participant-page__main">
      <div>
        <ul class="nav nav-tabs">
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
              This chime is linked to Canvas. To participate, join this chime by
              clicking the assignment link in your Canvas course.
            </p>

            <a class="btn btn-primary" :href="canvasCourseUrl">
              Go to Canvas
            </a>
            <p class="mt-3">
              <small class="text-muted">
                If you believe this message is in error, you may use
                <a href="#" @click.prevent="forceLoad = true">this link</a>
                to force the chime to load. You may not recieve credit for your
                responses. Please contact your instructor or
                <a href="mailto:help@umn.edu" class="text-muted">help@umn.edu</a
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
            <transition-group v-if="filteredSession.length > 0" name="fade">
              <ParticipantPrompt
                v-for="s in filteredSession"
                :key="s.id"
                :session="s"
                :chime="chime"
                :responses="responses"
                @updateResponse="updateResponse"
              />
            </transition-group>
          </template>
        </div>

        <!-- answered Questions -->
        <div id="pastQuestions" class="tab-pane container">
          <div v-if="responses.length < 1" class="text-center">
            <h1>No Answered Questions</h1>
          </div>
          <Response
            v-else
            v-for="(response, i) in sortedResponses"
            :key="i"
            :chime="chime"
            :response="response"
          />
        </div>
        <p class="text-center m-0" v-if="!ltiLaunchWarning">
          <small v-if="chime.lti_course_title" class="text-muted"
            >Not seeing the prompts you're looking for? Make sure you've
            followed the correct assignment link from Canvas.
          </small>
          <small
            v-if="chime.lti_course_title && chime.lti_grade_mode != 'no_grades'"
            class="text-muted"
            >Grades will generally be updated in Canvas within 4 hours.
          </small>
        </p>
      </div>
    </main>
  </div>
</template>

<script>
import get from "lodash/get";
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

export default {
  components: {
    ErrorDialog,
    NavBar,
    ParticipantPrompt,
    Response,
    ViewModeNotice,
  },
  props: ["user", "chimeId", "folderId"],
  data() {
    return {
      chime: {},
      sessions: [],
      responses: [],
      error: null,
      timeout: null,
      loadTime: null,
      forceLoad: false,
    };
  },
  computed: {
    canvasCourseUrl() {
      return selectCanvasCourseUrl(this.chime);
    },
    joinUrl() {
      return selectJoinUrl(this.chime);
    },
    isCanvasChime() {
      return selectIsCanvasChime(this.chime);
    },
    inParticipantView() {
      const viewMode = get(this, "$route.query.viewMode", null);
      if (!viewMode) return false;

      return viewMode.toLowerCase() === "participant";
    },
    filteredSession: function () {
      if (this.folderId) {
        return this.sessions.filter(
          (e) => e.question.folder_id == this.folderId
        );
      } else {
        return this.sessions;
      }
    },
    sortedResponses: function () {
      function compare(a, b) {
        if (a.updated_at > b.updated_at) return -1;
        if (a.updated_at < b.updated_at) return 1;
        return 0;
      }

      return [...this.responses].sort(compare);
    },
    ltiLaunchWarning: function () {
      if (
        !this.forceLoad &&
        !this.inParticipantView &&
        !window.lti_launch &&
        this.isCanvasChime
      ) {
        return true;
      }
      return false;
    },
  },
  mounted: function () {
    this.loadChime();
    this.loadTime = new Date();
    Echo.join("session-status." + this.chimeId)
      .listen("StartSession", (m) => {
        console.log("debug", "message:", m);
        this.sessions.unshift(m.session);
        this.$announcer.set(
          "A new question has been open.  There are " +
            this.sessions.length +
            " questions open"
        );
      })
      .listen("EndSession", (m) => {
        var removeIndex = this.sessions.findIndex(
          (session) => session.id == m.session.id
        );
        this.sessions.splice(removeIndex, 1);
        this.$announcer.set(
          "A question has been closed.  There are " +
            this.sessions.length +
            " questions open"
        );
      });

    window.Echo.connector.socket.on("reconnect", () => {
      console.log("reconnecting and reloading");
      if (this.timeout) clearTimeout(this.timeout);

      const hoursSinceLoad = (new Date() - this.loadTime) / 1000 / 60 / 60;
      if (hoursSinceLoad >= 8) {
        Echo.leave("session-status." + this.chimeId);
        window.Echo.connector.socket.off("reconnect");
        this.error = "Your session has expired.  Please refresh the page.";
        this.sessions = [];
        this.$announcer.set(
          "Your session has expired. Please refresh the page."
        );
        return;
      }

      this.timeout = setTimeout(() => {
        this.loadChime();
      }, 500);
    });
  },
  beforeDestroy: function () {
    Echo.leave("session-status." + this.chimeId);
    window.Echo.connector.socket.off("reconnect");
  },
  methods: {
    updateResponse: function (newResponse) {
      var updateInPlace = false;
      this.responses.forEach((response, index) => {
        if (response.id == newResponse.id) {
          updateInPlace = true;
          this.$set(this.responses, index, newResponse);
        }
      });
      if (!updateInPlace) {
        this.responses.push(newResponse);
      }
    },
    loadChime: function () {
      axios
        .get("/api/chime/" + this.chimeId + "/openQuestions")
        .then((res) => {
          console.log("debug", "chime:", res);
          this.chime = res.data.chime;
          document.title = this.chime.name;
          this.sessions = res.data.sessions.reverse();
        })
        .catch((err) => {
          if (err.response.data.status == "AttemptAuth") {
            window.location =
              "/loginAndRedirect?target=" + window.location.pathname;
          } else {
            if (err.response.data.message) {
              this.error = err.response.data.message;
            }
            this.$store.commit(
              "message",
              "Could not load Chime. You may not have permission to view this page. "
            );
            console.log("error getting chime:", err.response);
          }
        })
        .then(() => {
          return axios
            .get("/api/chime/" + this.chimeId + "/responses")
            .then((res) => {
              console.log("debug", "Response:", res);
              this.responses = res.data;
            });
        });
    },
  },
};
</script>

<style scoped>
.participant-page__main {
  border-top: 1px solid var(--neutral-300);
  background: var(--neutral-200);
}

.nav-link {
  border: 0;
  border-radius: 1rem;
  color: var(--neutral-500);
  font-size: 0.75rem;
  text-transform: uppercase;
  font-weight: bold;
  letter-spacing: 0.05rem;
}
.nav-link.active {
  background: white;
  color: var(--neutral-900);
}
.nav {
  display: flex;
  padding: 1rem;
  gap: 0.5rem;
  align-items: center;
  border-bottom: 0;
  border-bottom: 1px solid var(--neutral-300);
}
.tab-pane {
  padding: 2rem 1rem;
}
</style>
