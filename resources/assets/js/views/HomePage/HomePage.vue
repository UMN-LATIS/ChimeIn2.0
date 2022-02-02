<template>
  <div>
    <NavBar title="" :user="user" :link="'/'" />
    <div class="container">
      <div class="col-sm-12">
        <h1 class="user-greeting my-3">
          {{ welcome }}, {{ user.guest_user ? "Guest User" : user.name }}
        </h1>

        <div class="row">
          <div class="col-12 col-md-9">
            <div v-if="user.guest_user" class="alert alert-info" role="alert">
              <strong>New to ChimeIn 2?</strong>
              We've got documentation available on our
              <a
                href="https://umn-latis.github.io/ChimeIn2.0/"
                class="alert-link"
                >help site</a
              >. You can also
              <a href="mailto:latistecharch@umn.edu" class="alert-link"
                >drop us a line</a
              >
              and we can arrange a personal introduction.
            </div>

            <ChimePanel :user="user" />
          </div>

          <div class="col-12 col-md-3">
            <div class="card">
              <div class="card-header">Access Code</div>
              <div class="card-body">
                <div class="input-group mb-3">
                  <p>
                    Do you have an access code to join a Chime? Enter it below.
                  </p>
                  <input
                    id="access_code"
                    v-model="access_code"
                    type="text"
                    class="form-control"
                    name="access_code"
                    placeholder="Access Code"
                    @keyup.enter="join_chime"
                  />
                  <div class="input-group-append">
                    <button
                      class="btn btn-secondary"
                      type="button"
                      @click="join_chime"
                    >
                      Join
                    </button>
                  </div>
                  <div
                    v-if="requires_login"
                    class="alert alert-danger"
                    role="alert"
                  >
                    You must <a href="/login">log in</a> to join this Chime
                  </div>
                  <div
                    v-if="chime_not_found"
                    class="alert alert-danger"
                    role="alert"
                  >
                    We couldn't find a Chime associated with that code
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { EventBus } from "../../EventBus.js";
import NavBar from "../../components/NavBar.vue";
import ChimePanel from "./ChimePanel.vue";

// myUndefinedFunction();

export default {
  components: {
    NavBar,
    ChimePanel,
  },
  props: ["user"],
  data() {
    return {
      access_code: "",
      requires_login: false,
      chime_not_found: false,
      welcomePhrases: [
        "Hello",
        "Hi",
        "Greetings",
        "Ahoy-hoy",
        "Good day",
        "Howdy",
        "Welcome",
      ],
    };
  },
  computed: {
    welcome: function () {
      return this.welcomePhrases[
        Math.floor(Math.random() * this.welcomePhrases.length)
      ];
    },
  },
  methods: {
    join_chime() {
      console.log(this.access_code);
      this.requires_login = false;
      this.chime_not_found = false;
      axios
        .post("/join/" + this.access_code)
        .then((res) => {
          this.access_code = "";
          EventBus.$emit("chimesChanged");
          this.$router.push({
            name: "chimeStudent",
            params: { chimeId: res.data.id },
          });
        })
        .catch((err) => {
          if (err.response.data.requiresLogin) {
            this.requires_login = true;
          }
          if (err.response.data.chimeNotFound) {
            this.chime_not_found = true;
          }
          console.error("error", "Error in join chime:", err.response);
        });
    },
  },
};
</script>
