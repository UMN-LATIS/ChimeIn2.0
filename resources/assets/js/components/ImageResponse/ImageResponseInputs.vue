<template>
  <div>
    <div v-if="response.response_info">
      <div v-if="hasResponse" class="response">
        <h2 class="response-heading">Your Response</h2>
        <img
          data-cy="image-thumbnail"
          class="responsive-img imageContainer"
          :src="'/storage/' + response.response_info.image"
        />
      </div>
    </div>
    <ImageUploadDropbox
      v-if="chime"
      :imageSrc="tmpImageSrc"
      :uploadTo="`/api/chime/${chime.id}/image`"
      @imageuploaded="handleImageUploaded"
    >
      Drag here or <u>browse</u> to
      {{ hasResponse ? "replace" : "upload" }} your image.
    </ImageUploadDropbox>
    <p v-if="error">
      <strong>{{ error }}</strong>
    </p>
    <div
      v-if="
        question.allow_multiple &&
        !disabled &&
        response &&
        response.response_info
      "
      class="form-group"
    >
      <button class="btn btn-primary" @click="clear">
        Clear and Start a New Response
      </button>
    </div>
  </div>
</template>

<script>
import ImageUploadDropbox from "./ImageUploadDropbox.vue";
import { get } from "lodash";

export default {
  components: { ImageUploadDropbox },
  props: ["question", "response", "disabled", "chime"],
  data() {
    return {
      isInitial: this.response ? false : true,
      isSaving: false,
      create_new_response: false,
      error: null,
      tmpImageSrc: null,
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
    hasTmpImage() {
      return !!this.tmpImageSrc;
    },
  },
  methods: {
    clear: function () {
      this.create_new_response = true;
    },
    handleImageUploaded(imageSrc) {
      console.log({ imageSrc });
      this.tmpImageSrc = `/storage/${imageSrc}`;
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

<style>
.dropbox {
  outline: 2px dashed grey; /* the dash box */
  outline-offset: -10px;
  background: #eee;
  color: dimgray;
  padding: 10px 10px;
  min-height: 200px; /* minimum height */
  position: relative;
  cursor: pointer;
}

.input-file {
  opacity: 0; /* invisible but it's there! */
  width: 100%;
  height: 200px;
  position: absolute;
  cursor: pointer;
}

.dropbox:hover {
  background: #ccc; /* when mouse over to the drop zone, change color */
}

.dropbox p {
  font-size: 1.2em;
  text-align: center;
  padding: 50px 0;
}
</style>

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
  margin-bottom: ;
}
</style>
