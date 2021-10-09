<template>
  <div class="new-folder">
    <label for="createFolder" class="text-right col-form-label"
      >Add a folder</label
    >
    <div class="row">
      <div class="col-8 col-6-md">
        <input
          type="text"
          class="form-control new-folder__input"
          v-model="folder_name"
          @keyup.enter="new_folder"
          name="createFolder"
          id="createFolder"
          placeholder="Folder Name"
        />
      </div>
      <div class="col-1 col-2-md">
        <button
          data-cy="create-folder-button"
          type="button"
          class="btn btn-primary new-folder__button"
          v-on:click="new_folder"
        >
          Create
        </button>
      </div>
    </div>
    <div
      class="alert alert-warning mt-2"
      role="alert"
      v-if="chime.lti_grade_mode == 'multiple_grades'"
    >
      <strong>Warning:</strong> Creating a new folder will not add a new
      gradebook entry in Canvas. If you'd like to track participation by
      week/lecture in the gradebook, start by creating a new assignment in
      Canvas. See
      <a href="https://umn-latis.github.io/ChimeIn2.0/canvas.html">our help</a>
      or <a href="mailto:latistecharch@umn.edu">contact us</a> for assistance.
      You can still create folders to assist with your own organization.
    </div>
  </div>
</template>
<style>
.new-folder {
  max-width: 40rem;
}
.new-folder:focus-within .new-folder__input {
  box-shadow: 0 0.25rem 0.5rem rgb(0 0 0 / 20%);
}
.new-folder:focus-within .new-folder__button {
  box-shadow: 0 0.25rem 0.5rem #007bff;
}
</style>

<script>
export default {
  props: ["chime"],
  data: function() {
    return {
      folder_name: "",
    };
  },
  methods: {
    filter_folder: function() {
      // NOTE Filter folder mechanic muted
      // this.$emit('filterfolder', this.folder_name);
    },
    new_folder: function() {
      this.$emit("newfolder", this.folder_name);
      this.folder_name = "";
      document.activeElement.blur();
    },
  },
};
</script>
