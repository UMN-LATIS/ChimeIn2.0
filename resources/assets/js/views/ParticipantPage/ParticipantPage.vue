<template>
  <div>
    <NavBar title="Home" :user="user" :link="'/'" />
    <ErrorDialog />
    <div class="alert alert-warning" role="alert" v-if="error">
      {{ error }}
    </div>
    <vue-announcer />
    <div class="container">
      <div class="card">
        <div class="card-header text-center">
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
                >Closed Questions</a
              >
            </li>
          </ul>
        </div>
        <div class="card-body">
          <div class="tab-content">
            <div
              class="tab-pane container active"
              id="currentQuestions"
              aria-live="polite"
            >
              <div
                v-if="filteredSession.length < 1"
                key="none"
                class="text-center"
              >
                <h1>No Open Questions</h1>
              </div>
              <transition-group name="fade" v-if="filteredSession.length > 0">
                <ParticipantPrompt
                  v-for="s in filteredSession"
                  v-on:updateResponse="updateResponse"
                  :session="s"
                  :chime="chime"
                  :responses="responses"
                  :key="s.id"
                />
              </transition-group>
            </div>
            <div class="tab-pane container" id="pastQuestions">
              <div v-if="responses.length < 1" class="text-center">
                <h1>No Closed Questions</h1>
              </div>
              <Response v-else./ParticipantPrompt.vue v-for="(response, i) in
              sortedResponses" v-bind:key="i" :chime="chime"
              :response="response" />
            </div>
          </div>
          <p class="text-center m-0">
            <small class="text-muted" v-if="chime.lti_course_title"
              >Not seeing the prompts you're looking for? Make sure you've
              followed the correct assignment link from Canvas</small
            >
          </p>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.nav-item {
  width: 50%;
}
</style>
<script>
import ErrorDialog from "../../components/ErrorDialog.vue";
import NavBar from "../../components/NavBar.vue";
import ParticipantPrompt from "./ParticipantPrompt.vue";
import Response from "./ParticipantResponse.vue";

export default {
  components: {
    ErrorDialog,
    NavBar,
    ParticipantPrompt,
    Response,
  },
  data() {
    return {
      chime: {},
      sessions: [],
      responses: [],
      error: null,
      timeout: null,
    };
  },
  props: ["user", "chimeId", "folderId"],
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
        });

      axios.get("/api/chime/" + this.chimeId + "/responses").then((res) => {
        console.log("debug", "Response:", res);
        this.responses = res.data;
      });
    },
  },
  computed: {
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

      return this.responses.sort(compare);
    },
  },
  mounted: function () {
    this.loadChime();

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
      this.timeout = setTimeout(() => {
        this.loadChime();
      }, 500);
    });
  },
  beforeDestroy: function () {
    Echo.leave("session-status." + this.chimeId);
    window.Echo.connector.socket.off("reconnect");
  },
};
</script>
