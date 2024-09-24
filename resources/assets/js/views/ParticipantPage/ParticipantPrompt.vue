<template>
  <article
    v-if="question.question_info.question_type"
    class="participant-prompt tw-relative tw-mb-8 sm:tw-px-8"
    :aria-label="`${questionTypeString} Question`"
    :class="{
      'save-succeeded': hasPreviouslySaved || saveStatus === 'success',
      'save-failed': saveStatus === 'error',
      'is-saving': saveStatus === 'saving',
    }"
  >
    <div
      class="tw-flex tw-gap-2 tw-items-center tw-mb-2"
      :class="{
        'tw-text-green-600': saveStatus === 'success',
        'tw-text-red-500': saveStatus === 'error',
        'tw-text-amber-600': saveStatus === 'saving',
        'tw-text-neutral-400': !saveStatus,
      }"
    >
      <IconCheckboxChecked v-if="saveStatus === 'success'" />
      <IconLoading v-else-if="saveStatus === 'saving'" />
      <IconWarningSquare v-else-if="saveStatus === 'error'" />
      <span v-else>•</span>
      <span class="tw-text-xs tw-uppercase tw-font-bold">{{
        saveStatusMessage || questionTypeString
      }}</span>

      <Chip
        v-if="saveStatus !== 'success'"
        class="!tw-text-umn-maroon !tw-border-umn-maroon"
      >
        Unanswered
      </Chip>
    </div>
    <div class="tw-pl-6">
      <div class="prompt-question-container tw-mb-2">
        <div
          :id="`question-${question.id}-heading`"
          role="heading"
          aria-level="3"
        >
          <div class="question-text" v-html="question.text" />
        </div>
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
        ><strong>Folder</strong>: {{ session.question.folder?.name }}
      </small>
    </div>
  </article>
</template>

<script lang="ts">
import MultipleChoice from "../../components/MultipleChoice/MultipleChoiceInputs.vue";
import ImageResponse from "../../components/ImageResponse/ImageResponseInputs.vue";
import FreeResponse from "../../components/FreeResponse/FreeResponseInputs.vue";
import TextHeatmapResponse from "../../components/TextHeatmap/TextHeatmapResponseInputs.vue";
import NoResponse from "../../components/NoResponse/NoResponseInputs.vue";
import SliderResponse from "../../components/SliderResponse/SliderResponseInputs.vue";
import ImageHeatmapResponse from "../../components/ImageHeatmapResponse/ImageHeatmapResponseInputs.vue";
import NumericResponse from "../../components/NumericResponse/NumericResponseInputs.vue";
import { PropType } from "vue";
import * as T from "@/types";
import axios from "@/common/axiosClient";
import Chip from "@/components/Chip.vue";
import {
  IconCheckboxChecked,
  IconCheckboxUnchecked,
  IconLoading,
  IconWarningSquare,
} from "@/icons";

export default {
  components: {
    multiple_choice: MultipleChoice,
    image_response: ImageResponse,
    text_heatmap_response: TextHeatmapResponse,
    no_response: NoResponse,
    free_response: FreeResponse,
    slider_response: SliderResponse,
    heatmap_response: ImageHeatmapResponse,
    numeric_response: NumericResponse,
    Chip,
    IconCheckboxChecked,
    IconCheckboxUnchecked,
    IconLoading,
    IconWarningSquare,
  },
  props: {
    session: {
      type: Object as PropType<T.Session>,
      required: true,
    },
    chime: {
      type: Object as PropType<T.Chime>,
      required: true,
    },
    responses: {
      type: Array as PropType<T.Response[]>,
      required: true,
    },
  },
  emits: ["updateResponse"],
  data: function () {
    return {
      responseUpdated: false,
      saveStatus: null as "success" | "error" | "saving" | null,
      error: null as null | string,
    };
  },
  computed: {
    question(): T.Question {
      return this.session.question;
    },
    hasPreviouslySaved(): boolean {
      if (!this.responses) return false;

      const sessionResponses = this.responses.filter(
        (response) => response.session_id === this.session.id
      );

      return Boolean(sessionResponses.length);
    },
    response(): T.Response | null {
      if (this.responses.length > 0 && this.session) {
        let foundResponse = null as null | T.Response;
        let sessionResponses = this.responses.filter(
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

      return null;
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
    saveStatusMessage(): string | null {
      const statusToMessage = {
        saving: "Saving...",
        error: "Error",
        success: "Saved",
      };

      return this.saveStatus ? statusToMessage[this.saveStatus] : null;
    },
  },
  watch: {
    responses: {
      handler() {
        if (!this.responses) return;
        console.log("hasPreviouslySaved", {
          hasPreviouslySaved: this.hasPreviouslySaved,
          chime: this.chime,
          session: this.session,
          responses: this.responses,
        });

        // initialize the save status
        this.saveStatus = this.hasPreviouslySaved ? "success" : null;
      },
      immediate: true,
    },
  },
  methods: {
    record_response: function (response, newResponse = false) {
      let url =
        "/api/chime/" +
        this.chime.id +
        "/session/" +
        this.session.id +
        "/response";

      if (this.response?.id && !newResponse) {
        url = url + "/" + this.response.id;
      }

      this.saveStatus = "saving";

      axios
        .put(url, { response_info: response })
        .then((res) => {
          this.saveStatus = "success";
          this.$emit("updateResponse", res.data);
          this.responseUpdated = true;
          setTimeout(() => {
            this.responseUpdated = false;
          }, 1500);
        })
        .catch((err) => {
          this.saveStatus = "error";
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
.updated-alert {
  position: absolute;
  bottom: 0.5rem;
  left: 50%;
  transform: translateX(-50%);
  max-width: 20rem;
  background: var(--gold-light);
}
</style>
