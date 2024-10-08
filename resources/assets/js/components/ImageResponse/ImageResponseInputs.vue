<template>
  <div class="image-response-input">
    <section v-if="hasResponse" class="response">
      <figure class="response__figure">
        <img
          data-cy="image-response-thumbnail"
          class="responsive-img imageContainer"
          :src="'/storage/' + response?.response_info.image"
          :alt="response?.response_info.image_alt"
        />
        <figcaption
          v-if="response?.response_info.image_alt"
          class="response__figcaption"
        >
          {{ response.response_info.image_alt }}
        </figcaption>
      </figure>
    </section>

    <div v-if="isOpenQuestion">
      <div class="dropbox-group">
        <ImageUploadDropbox
          v-if="chime"
          :aria-labelledby="`question-${question.id}-heading`"
          class="dropbox-group__uploader"
          :imageSrc="tempImagePath ?? undefined"
          :uploadTo="`/api/chime/${chime.id}/image`"
          @imageUploaded="handleImageUploaded"
          @removePreviewImage="handlePreviewRemoveImage"
        >
          Drag here or <u>browse</u> to select your image.
        </ImageUploadDropbox>
        <TextAreaInput
          v-if="isOpenQuestion"
          v-model="imageAlt"
          class="dropbox-group__alt-input"
          label="Alt Text"
          name="alt-text"
          placeholder="Describe your image"
          visuallyHideLabel
          data-cy="alt-text-input"
        />
      </div>
    </div>
    <footer v-if="isOpenQuestion" class="image-response__footer">
      <button
        class="btn btn-outline-primary"
        :disabled="!hasTempImage"
        @click="handleSave"
      >
        {{ saveButtonText }}
      </button>

      <!-- when multiple responses are allowed, this button allows a user to override creating a new response and instead replace previous response -->
      <button
        v-if="question.allow_multiple"
        class="btn btn-link"
        :disabled="!hasTempImage"
        @click="handleUpdatePreviousResponse"
      >
        Update Previous
      </button>
    </footer>
  </div>
</template>

<script lang="ts">
import ImageUploadDropbox from "./ImageUploadDropbox.vue";
import TextAreaInput from "../TextAreaInput.vue";
import { PropType } from "vue";
import * as T from "@/types";

export default {
  components: { ImageUploadDropbox, TextAreaInput },
  props: {
    chime: {
      type: Object as PropType<T.Chime>,
      required: true,
    },
    question: {
      type: Object as PropType<T.Question<T.ImageResponseQuestionInfo>>,
      required: true,
    },
    response: {
      type: Object as PropType<T.Response<T.ImageResponseResponseInfo> | null>,
      required: false,
      default: null,
    },
    disabled: { type: Boolean, default: false },
  },
  emits: ["recordresponse"],
  data() {
    return {
      isSaving: false,
      tempImageSrc: null as string | null,
      tempImageName: null as string | null,
      imageAlt: "",
    };
  },
  computed: {
    responseImage() {
      const filename = this.response?.response_info.image;
      return {
        src: filename ? `/storage/${filename}` : null,
        alt: this.response?.response_info.image_alt || "",
      };
    },
    shouldAddNewResponse() {
      return this.question.allow_multiple;
    },
    hasResponse() {
      return !!this.responseImage.src;
    },
    hasTempImage() {
      return !!this.tempImageSrc;
    },
    isOpenQuestion() {
      return !this.disabled;
    },
    tempImagePath() {
      return this.tempImageSrc ? `/storage/${this.tempImageSrc}` : null;
    },
    saveButtonText() {
      if (!this.hasResponse) {
        return "Save";
      }

      if (this.question.allow_multiple) {
        return "Add Response";
      }

      return "Update";
    },
  },
  methods: {
    resetForm() {
      this.tempImageSrc = null;
      this.tempImageName = null;
      this.imageAlt = "";
    },
    handleImageUploaded({ src, name }) {
      this.tempImageSrc = src;
      this.tempImageName = name;
    },
    handlePreviewRemoveImage() {
      this.tempImageSrc = null;
      this.tempImageName = null;
    },
    handleUpdatePreviousResponse() {
      const response = {
        question_type: "image_response",
        image: this.tempImageSrc,
        image_name: this.tempImageName,
        image_alt: this.imageAlt,
      };

      // override shouldAddNewResponse by setting last arg to false
      // this will allow the user to replace the previous response with this one
      this.$emit("recordresponse", response, false);
      this.resetForm();
    },
    handleSave() {
      const response = {
        question_type: "image_response",
        image: this.tempImageSrc,
        image_name: this.tempImageName,
        image_alt: this.imageAlt,
      };

      this.$emit("recordresponse", response, this.shouldAddNewResponse);
      this.resetForm();
    },
  },
};
</script>

<style scoped>
.response-heading {
  text-transform: uppercase;
  font-size: 0.75rem;
  letter-spacing: 0.5px;
  font-weight: bold;
  color: #777;
  margin: 0;
}
.response-header {
  margin-bottom: 0.5rem;
}

.imageContainer {
  padding: 0.5rem;
}

.response__figure {
  display: inline-block;
  border: 1px solid #ccc;
  margin-bottom: 1rem;
}

.response__figcaption {
  font-size: 0.75rem;
  padding: 1rem;
  background: #eee;
}

img {
  display: block;
  max-width: 400px;
  max-height: 400px;
}

.card {
  max-width: 30rem;
}
.dropbox-group {
  background: #eee;
}
.dropbox-group__alt-input {
  padding: 0.5rem;
  margin-top: -0.25rem;
}
</style>

<style>
.dropbox-group__alt-input textarea {
  background: #fafafa;
  font-size: 0.75rem;
  border-radius: 0;
}
</style>
