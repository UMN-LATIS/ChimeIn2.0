<template>
  <div class="row questionContainer">
    <div v-if="question.question_info.question_type" class="col-12">
      <p class="quesiton-text" v-html="question.text"></p>

      <component
        :is="question.question_info.question_type"
        :question="question"
        :response="response"
        :chime="chime"
        :disabled="false"
        @recordresponse="record_response"
      >
      </component>
      <transition name="fade">
        <p v-if="responseUpdated" class="alert alert-info">Response Updated</p>
      </transition>
      <p v-if="error" class="alert alert-warning">{{ error }} Please reload.</p>

      <small
        v-if="chime.show_folder_title_to_participants"
        class="text-muted"
        data-cy="show-folder-to-participants"
        ><strong>Folder</strong>: {{ session.question.folder.name }}
      </small>
      <hr />
    </div>
    <!-- <div class="card-content" v-else>
            <span class="card-title">
                <h4>'No Question Yet!'</h4>
            </span>
        </div> -->
  </div>
</template>

<style>
.questionContainer {
  /*border: 1px solid black;*/
  /*border-radius: 5px;*/
  /*margin: 5px;*/
}
</style>

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
  data: function () {
    return {
      responseUpdated: false,
      error: null,
      question: {
        question_info: "",
      },
    };
  },
  computed: {
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
  },
  created: function () {
    this.question = this.session.question;
  },
  methods: {
    record_response: function (response, newResponse = false) {
      const self = this;

      var url =
        "/api/chime/" +
        this.chime.id +
        "/session/" +
        this.session.id +
        "/response";

      if (this.response.id && !newResponse) {
        url = url + "/" + this.response.id;
      }

      axios
        .put(url, { response_info: response })
        .then((res) => {
          console.log("debug", "response recorded:", res);
          this.$emit("updateResponse", res.data);
          this.responseUpdated = true;
          setTimeout(() => {
            this.responseUpdated = false;
          }, 1500);
        })
        .catch((err) => {
          console.error("error", "error recording response", err.response);
          if (!err.response) {
            this.error =
              "Error recording response. Your internet connection may be down. ";
          } else {
            this.error = err.response;
          }
        });
      // document.activeElement.blur();
    },
  },
};
</script>

<style scoped>
li {
  font-size: 1.5em;
}

.col.s12 > .btn {
  width: 100%;
}
</style>
