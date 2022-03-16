<template>
  <div
    class="image-upload"
    :class="{
      'image-upload--has-image': hasImage,
    }"
  >
    <div
      v-if="errorText"
      class="image-upload__error alert alert-danger"
      role="alert"
    >
      <strong>Error:</strong>
      {{ errorText }}
    </div>
    <div class="image-upload__dropbox">
      <img
        v-if="hasImage"
        class="image-upload__preview"
        :src="imageSrc"
        alt="preview of image to be submitted with response"
      />
      <h3 v-if="hasImage" class="image-upload__dropbox-heading">
        Response Preview
      </h3>

      <input
        type="file"
        class="image-upload__input"
        @change="onFileChange"
        id="image-upload-file"
        accept="image/gif, image/png, image/jpeg, image/webp"
      />

      <div class="image-upload__status">
        <slot v-if="!isUploading">
          Drag your image here or
          <u>browse</u> to {{ hasImage ? "replace" : "upload" }}.
        </slot>
        <div class="image-upload__spinner" v-if="isUploading">
          <i class="fas fa-spinner fa-spin fa-3x"></i>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  props: {
    imageSrc: {
      type: String,
      default: null,
    },
    uploadTo: {
      type: String,
      required: true,
    },
  },
  emits: ["imageuploaded"],
  data() {
    return {
      isUploading: false,
      errorText: null,
    };
  },
  computed: {
    hasImage() {
      return !!this.imageSrc;
    },
  },
  methods: {
    onFileChange(e) {
      const selectedFile = e.target.files[0];
      this.errorText = null;
      this.isUploading = true;

      const formData = new FormData();
      formData.append("image", selectedFile);

      axios
        .post(this.uploadTo, formData)
        .then((response) => {
          console.log({ response });
          this.isUploading = false;
          this.$emit("imageuploaded", {
            src: response.data.image,
            name: selectedFile.name,
          });
        })
        .catch((error) => {
          console.error({ error });
          this.errorText = error.message;
          this.isUploading = false;
        });
    },
  },
};
</script>
<style scoped>
.image-upload__preview {
  max-width: 200px;
  max-height: 200px;
  position: relative;
  padding: 0.5rem;
  border: 1px solid #ccc;
  background: #fff;
}

.image-upload__dropbox {
  /* border: 2px dashed #aaa; */
  outline: 2px dashed #aaa;
  outline-offset: -0.5rem;
  background: hsla(0, 0%, 0%, 0.05);
  color: #333;
  min-height: 150px; /* minimum height */
  position: relative;
  cursor: pointer;
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  text-align: center;
  padding: 1rem;
}

.image-upload__input {
  opacity: 0; /* invisible but it's there! */
  width: 100%;
  height: 100%;
  cursor: pointer;
  position: absolute;
  top: 0;
  left: 0;
  z-index: 10;
}

.image-upload__status {
  font-size: 1rem;
  color: #777;
  margin: 0;
}

.image-upload__dropbox-heading {
  text-transform: uppercase;
  font-size: 0.75rem;
  letter-spacing: 0.5px;
  font-weight: bold;
  color: #777;
  margin: 0.5rem 0;
}
</style>
