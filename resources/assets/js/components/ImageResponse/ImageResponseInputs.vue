<template>
  <div class="image-response-input">
    <div class="card">
      <div class="card-body">
        <section v-if="hasResponse" class="response">
          <h2 class="response-heading">Your Response</h2>
          <img
            data-cy="image-thumbnail"
            class="responsive-img imageContainer"
            :src="'/storage/' + response.response_info.image"
            :alt="response.response_info.image_alt"
          />
        </section>

        <div v-if="isOpenQuestion">
          <ImageUploadDropbox
            class="image-response__dropbox"
            v-if="chime"
            :imageSrc="tempImageSrc"
            :uploadTo="`/api/chime/${chime.id}/image`"
            @imageuploaded="handleImageUploaded"
          >
            Drag here or <u>browse</u> to
            {{ hasResponse ? "replace" : "upload" }} your image.
          </ImageUploadDropbox>
          <TextAreaInput
            v-if="isOpenQuestion"
            label="Alt Text"
            name="alt-text"
            value=""
            placeholder="Describe your image"
          >
            <template #description>
              Used by screenreaders to describe the contents of the image.
              <a
                href="https://accessibility.umn.edu/what-you-can-do/start-7-core-skills/alternative-text"
                >Learn more...</a
              >
            </template>
          </TextAreaInput>

          <p v-if="error">
            <strong>{{ error }}</strong>
          </p>
        </div>
      </div>
      <footer class="image-response__footer card-footer" v-if="isOpenQuestion">
        <button class="btn btn-outline-primary" :disabled="!hasTempImage">
          {{ hasResponse ? "Update" : "Save" }}
        </button>

        <button class="btn btn-link" :disabled="!hasTempImage">Clear</button>
      </footer>
    </div>
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
      error: null,
      tempImageSrc: null,
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
  },
  methods: {
    clear: function () {
      this.create_new_response = true;
    },
    handleImageUploaded(imageSrc) {
      console.log({ imageSrc });
      this.tempImageSrc = `/storage/${imageSrc}`;
    },
    attachFile: function (event, fileList) {
      this.isSaving = true;
      this.isInitial = false;
      let formData = new FormData();
      Array.from(Array(fileList.length).keys()).map((x) => {
        formData.append("image", fileList[x], fileList[x].name);
      });
      axios
        .post("/api/chime/" + this.chime.id + "/image", formData)
        .then((res) => {
          const response = {
            question_type: "image_response",
            image: res.data.image,
            image_name: fileList[0].name,
          };
          this.isSaving = false;
          // this.isInitial= true;
          this.$emit("recordresponse", response, this.create_new_response);
          this.create_new_response = false;
          this.error = null;
        })
        .catch((err) => {
          if (err.response) {
            this.error = err.response.data.message;
          }
        });
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
  margin: 0.5rem 0;
}

.imageContainer {
  max-width: 200px;
  max-height: 200px;
  padding: 0.5rem;
  border: 1px solid #ccc;
  margin-bottom: 1rem;
}

.card {
  max-width: 30rem;
}
.image-response__dropbox {
  margin-bottom: 1rem;
}
</style>
