<template>
  <Modal :show="show" @close="close">
    <div class="modal-header" data-cy="add-question-form">
      <h3>Add a Question</h3>
    </div>
    <div class="modal-body">
      <div class="row">
        <div class="col-sm-3">
          <label for="folder" class="col-form-label">Folder</label>
        </div>
        <div class="col-sm-9">
          <div v-if="folders" class="form-group">
            <VSelect
              v-model="folder_id"
              :options="folders"
              label="name"
              :reduce="(folder) => folder.id"
              :clearable="false"
            ></VSelect>
          </div>
        </div>
        <div class="col-sm-3">
          <label for="questionType" class="col-form-label">Question Type</label>
        </div>
        <div class="col-sm-9">
          <div class="form-group">
            <VSelect
              v-model="question_type"
              data-cy="question-type"
              :options="question_types"
              :reduce="(question_type) => question_type.id"
              :clearable="false"
            ></VSelect>
          </div>
        </div>
      </div>
      <div class="row">
        <div class="col-6">
          <div class="form-check">
            <label class="form-check-label">
              <input
                v-model="anonymous"
                type="checkbox"
                class="form-check-input"
              />
              Anonymous Question
            </label>
          </div>
        </div>
        <div class="col-6">
          <div class="form-check">
            <label class="form-check-label">
              <input
                v-model="allow_multiple"
                type="checkbox"
                class="form-check-input"
                data-cy="allow-multiple-responses-checkbox"
              />
              Allow Multiple Responses
            </label>
          </div>
        </div>
      </div>
      <hr />
      <VEditor
        v-model="question_text"
        data-cy="question-editor"
        placeholder="Question Text"
        :imageUploadUrl="`/api/chime/${folder.chime_id}/image`"
      />

      <component
        :is="question_type + '_response'"
        v-if="question_type !== 'image_response'"
        v-model:question_responses="question_responses"
        :chime_id="folder.chime_id"
      ></component>
    </div>
    <footer class="question-form__footer">
      <div class="question-form__footer-col">
        <button class="btn btn-danger" @click="reset">Reset Question</button>
      </div>
      <div class="question-form__footer-col">
        <button class="btn btn-secondary" @click="close">Cancel</button>
        <button
          class="btn btn-primary"
          :disabled="!question_text.length"
          @click="savePost"
        >
          Save
        </button>
      </div>
    </footer>
  </Modal>
</template>

<script>
import katex from "katex";
window.katex = katex;

import MultipleChoiceQuestionOptions from "./MultipleChoiceQuestionOptions.vue";
import SliderResponseQuestionOptions from "./SliderResponseQuestionOptions.vue";
import FreeResponseQuestionOptions from "./FreeResponseQuestionOptions.vue";
import TextHeatmapResponseQuestionOptions from "./TextHeatmapResponseQuestionOptions.vue";
import HeatmapResponseQuestionOptions from "./HeatmapResponseQuestionOptions.vue";
import NoResponseQuestionOptions from "./FreeResponseQuestionOptions.vue";
import Modal from "../../components/Modal.vue";
import VSelect from "../../components/VSelect.vue";
import VEditor from "../../components/VEditor.vue";

export default {
  components: {
    VEditor,
    VSelect,
    multiple_choice_response: MultipleChoiceQuestionOptions,
    slider_response_response: SliderResponseQuestionOptions,
    free_response_response: FreeResponseQuestionOptions,
    text_heatmap_response_response: TextHeatmapResponseQuestionOptions,
    heatmap_response_response: HeatmapResponseQuestionOptions,
    no_response_response: NoResponseQuestionOptions,
    Modal,
  },
  props: ["question", "show", "folder", "controlType"],
  emits: ["close", "edited"],
  data: function () {
    return {
      folders: null,
      choice_text: "",
      question_text: this.question.text,
      question_type: this.question.question_info.question_type,
      question_responses: this.question.question_info.question_responses,
      folder_id: this.folder.id,
      anonymous: this.question.anonymous,
      allow_multiple: this.question.allow_multiple,
      question_types: [
        {
          id: "multiple_choice",
          label: "Multiple Choice",
        },
        {
          id: "free_response",
          label: "Free Response",
        },
        {
          id: "text_heatmap_response",
          label: "Text Heatmap",
        },
        {
          id: "image_response",
          label: "Image Response",
        },
        {
          id: "slider_response",
          label: "Slider",
        },
        {
          id: "heatmap_response",
          label: "Heatmap",
        },
        {
          id: "no_response",
          label: "No Response (placeholder)",
        },
      ],
      editorOptions: {
        bounds: ".modal-body",
        modules: {
          formula: true,
          keyboard: {
            bindings: {
              "list autofill": {
                prefix: /^\s{0,}(1){1,1}(\.|-|\*|\[ ?\]|\[x\])$/,
              },
            },
          },
        },
      },
    };
  },
  mounted() {
    axios
      .get("/api/chime/" + this.folder.chime_id)
      .then((res) => {
        this.folders = res.data.folders;
      })
      .catch((err) => {
        this.$store.commit(
          "message",
          "Could not load this form. The full error was: " +
            JSON.stringify(err.response).slice(0, 100)
        );
      });
  },
  methods: {
    close: function () {
      this.$emit("close");
    },
    reset: function () {
      if (
        confirm(
          "Are you sure you want to reset this question, clearing all sessions and responses?"
        )
      ) {
        const url =
          "/api/chime/" +
          this.folder.chime_id +
          "/folder/" +
          this.folder.id +
          "/question/" +
          this.question.id +
          "/responses";
        axios.delete(url).then(() => {
          this.$emit("edited");
        });
      }
    },
    savePost: function () {
      var url =
        "/api/chime/" + this.folder.chime_id + "/folder/" + this.folder.id;

      var question = {};
      question.text = this.question_text;

      // remove any blanks from mult choice reponses
      if (this.question_type === "multiple_choice") {
        this.question_responses = this.question_responses.filter(
          (r) => r.text !== ""
        );
      }

      question.question_info = {
        question_type: this.question_type,
        question_responses: this.question_responses,
      };
      var responseBlock = {
        question_text: question.text,
        question_info: question.question_info,
        anonymous: this.anonymous,
        folder_id: this.folder_id,
        allow_multiple: this.allow_multiple,
      };

      if (this.question.id) {
        url = url + "/question/" + this.question.id;
        axios
          .put(url, responseBlock)
          .then(() => {
            this.$emit("edited");
          })
          .catch((err) => {
            this.$store.commit(
              "message",
              "Could not store this question.  The full error was: " +
                JSON.stringify(err.response).slice(0, 100)
            );
          });
      } else {
        axios
          .post(url, responseBlock)
          .then(() => {
            this.close();
          })
          .catch((err) => {
            this.$store.commit(
              "message",
              "Could not store this question.  The full error was: " +
                JSON.stringify(err.response).slice(0, 100)
            );
          });
      }
    },
  },
};
</script>

<style scoped>
.deleteIcon {
  vertical-align: middle !important;
}

.choiceRow {
  margin-top: 5px;
  margin-bottom: 5px;
}

.question-form__footer {
  margin-top: 2rem;
  border-top: 1px solid #ccc;
  padding-top: 1.25rem;
  padding-bottom: 0.5rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
}
.question-form__footer-col {
  display: flex;
  gap: 0.5rem;
}
</style>
