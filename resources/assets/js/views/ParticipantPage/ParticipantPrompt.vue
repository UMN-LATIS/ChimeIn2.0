<template>
  <div
    v-if="question.question_info.question_type"
    class="participant-prompt"
    :class="{
      'save-succeeded': hasPreviouslySaved || saveSucceeded,
      'save-failed': saveFailed,
      'is-saving': isSaving,
    }"
  >
    <div class="prompt-question-container">
      <header class="prompt-header">
        {{ saveStatus || questionTypeString }}
      </header>

      <div class="question-text" v-html="question.text" />
    </div>

    <div class="prompt-response-area">
      <component
        :is="question.question_info.question_type"
        :question="question"
        :response="response"
        :chime="chime"
        :disabled="false"
        @recordresponse="record_response"
      />
    </div>

    <Transition name="fade">
      <p v-if="responseUpdated" class="updated-alert alert alert-info">
        Response Updated
      </p>
    </Transition>

    <p v-if="error" class="alert alert-danger">
      {{ error }} Please try reloading the page, or contact
      <a href="mailto:help@umn.edu">help@umn.edu</a>. If possible, include a
      screenshot of this error.
    </p>

    <small
      v-if="chime.show_folder_title_to_participants"
      class="text-muted"
      data-cy="show-folder-to-participants"
      ><strong>Folder</strong>: {{ session.question.folder.name }}
    </small>
  </div>
</template>

<script>
import MultipleChoice from "../../components/MultipleChoice/MultipleChoiceInputs.vue";
import ImageResponse from "../../components/ImageResponse/ImageResponseInputs.vue";
import FreeResponse from "../../components/FreeResponse/FreeResponseInputs.vue";
import TextHeatmapResponse from "../../components/TextHeatmap/TextHeatmapResponseInputs.vue";
import NoResponse from "../../components/NoResponse/NoResponseInputs.vue";
import SliderResponse from "../../components/SliderResponse/SliderResponseInputs.vue";
import ImageHeatmapResponse from "../../components/ImageHeatmapResponse/ImageHeatmapResponseInputs.vue";

export default {
  components: {
    multiple_choice: MultipleChoice,
    image_response: ImageResponse,
    text_heatmap_response: TextHeatmapResponse,
    no_response: NoResponse,
    free_response: FreeResponse,
    slider_response: SliderResponse,
    heatmap_response: ImageHeatmapResponse,
  },
  props: ["session", "chime", "responses"],
  emits: ["updateResponse"],
  data: function () {
    return {
      responseUpdated: false,
      saveSucceeded: false,
      saveFailed: false,
      isSaving: false,
      error: null,
      question: {
        question_info: "",
      },
    };
  },
  computed: {
    hasPreviouslySaved() {
      if (!this.responses) return false;

      const sessionResponses = this.responses.filter(
        (response) => response.session_id === this.session.id
      );

      return sessionResponses.length;
    },
    response: function () {
      if (this.responses.length > 0 && this.session) {
        var foundResponse = null;
        var sessionResponses = this.responses.filter(
          (r) => r.session_id == this.session.id
        );
        if (sessionResponses.length > 0) {
          foundResponse = sessionResponses.reduce((prev, current) => {
            return prev.id > current.id ? prev : current;
          });
        }

        if (foundResponse) {
          return foundResponse;
        }
      }

      return {};
    },
    questionType() {
      return this.question.question_info.question_type;
    },
    questionTypeString() {
      return this.questionType
        .split("_")
        .map((str) => str.charAt(0).toUpperCase() + str.slice(1))
        .join(" ");
    },
    saveStatus() {
      if (this.isSaving) {
        return "Saving...";
      }
      if (this.saveFailed) {
        return "Error";
      }
      if (this.hasPreviouslySaved || this.saveSucceeded) {
        return "Saved";
      }

      return "";
    },
  },
  created: function () {
    this.question = this.session.question;
  },
  methods: {
    record_response: function (response, newResponse = false) {
      var url =
        "/api/chime/" +
        this.chime.id +
        "/session/" +
        this.session.id +
        "/response";

      if (this.response.id && !newResponse) {
        url = url + "/" + this.response.id;
      }

      this.isSaving = true;
      this.saveSucceeded = false;
      this.saveFailed = false;

      axios
        .put(url, { response_info: response })
        .then((res) => {
          this.isSaving = false;
          this.saveSucceeded = true;
          this.$emit("updateResponse", res.data);
          this.responseUpdated = true;
          setTimeout(() => {
            this.responseUpdated = false;
          }, 1500);
        })
        .catch((err) => {
          this.isSaving = false;
          this.saveFailed = true;
          console.error("error", "error recording response", err.response);
          if (!err.response) {
            this.error =
              "Error recording response. Your internet connection may be down. ";
          } else {
            if (err.response.data && err.response.data.message) {
              this.error = err.response.data.message;
            } else {
              this.error = err.response;
            }
          }
        });
    },
  },
};
</script>

<style scoped>
.participant-prompt {
  position: relative;
  margin-bottom: 2rem;
  padding-left: 1.25rem;
}
.participant-prompt:before {
  content: "";
  display: block;
  height: 0.8rem;
  width: 0.8rem;
  background: #ccc;
  position: absolute;
  left: 0;
  top: 0;
}

.save-succeeded.participant-prompt:before {
  background: #31d158;
}
.is-saving.participant-prompt:before {
  background: var(--gold);
}
.save-failed.participant-prompt:before {
  background: var(--red);
}

.prompt-header {
  line-height: 1;
  position: relative;
  text-transform: uppercase;
  font-weight: bold;
  color: #999;
  font-size: 0.8rem;
  display: flex;
  justify-content: space-between;
  margin-bottom: 0.5rem;
}

.save-succeeded .prompt-header {
  color: #31d158;
}
.is-saving .prompt-header {
  color: var(--gold);
}
.save-failed .prompt-header {
  color: var(--red);
}

.updated-alert {
  position: absolute;
  bottom: 0.5rem;
  left: 50%;
  transform: translateX(-50%);
  max-width: 20rem;
  background: var(--gold-light);
}
</style>
