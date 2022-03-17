<template>
  <div class="image-response-input">
    <section v-if="hasResponse" class="response">
      <figure class="response__figure">
        <img
          data-cy="image-response-thumbnail"
          class="responsive-img imageContainer"
          :src="'/storage/' + response.response_info.image"
          :alt="response.response_info.image_alt"
        />
        <figcaption
          class="response__figcaption"
          v-if="response.response_info.image_alt"
        >
          {{ response.response_info.image_alt }}
        </figcaption>
      </figure>
    </section>

    <div v-if="isOpenQuestion">
      <div class="dropbox-group">
        <ImageUploadDropbox
          class="dropbox-group__uploader"
          v-if="chime"
          :imageSrc="tempImagePath"
          :uploadTo="`/api/chime/${chime.id}/image`"
          @imageuploaded="handleImageUploaded"
        >
          Drag here or <u>browse</u> to
          {{ hasResponse ? "replace" : "upload" }} your image.
        </ImageUploadDropbox>
        <TextAreaInput
          v-if="isOpenQuestion"
          class="dropbox-group__alt-input"
          label="Alt Text"
          name="alt-text"
          v-model="imageAlt"
          placeholder="Describe your image"
          visuallyHideLabel
          data-cy="alt-text-input"
        />
      </div>
    </div>
    <footer class="image-response__footer" v-if="isOpenQuestion">
      <button
        class="btn btn-outline-primary"
        :disabled="!hasTempImage"
        @click="handleSave"
      >
        {{ saveButtonText }}
      </button>

      <button class="btn btn-link" :disabled="!hasTempImage">Clear</button>
    </footer>
  </div>
</template>

<script>
import ImageUploadDropbox from "./ImageUploadDropbox.vue";
import TextAreaInput from "../TextAreaInput.vue";
import { get } from "lodash";

export default {
  components: { ImageUploadDropbox, TextAreaInput },
  props: ["question", "response", "disabled", "chime"],
  data() {
    return {
      isInitial: this.response ? false : true,
      isSaving: false,
      create_new_response: false,
      tempImageSrc: null,
      tempImageName: null,
      imageAlt: "",
    };
  },
  computed: {
    responseImage() {
      const filename = get(this.response, "response_info.image", null);
      return {
        src: filename ? `/storage/${filename}` : null,
        alt: get(this.response, "response_info.image_alt", ""),
      };
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
      return this.hasTempImage ? `/storage/${this.tempImageSrc}` : null;
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
    handleImageUploaded({ src, name }) {
      this.tempImageSrc = src;
      this.tempImageName = name;
    },
    handleSave() {
      const response = {
        question_type: "image_response",
        image: this.tempImageSrc,
        image_name: this.tempImageName,
        image_alt: this.imageAlt,
      };

      this.$emit("recordresponse", response, this.create_new_response);
      this.create_new_response = false;
      this.tempImageSrc = null;
      this.tempImageName = null;
      this.imageAlt = null;
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
  max-width: 100%;
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
