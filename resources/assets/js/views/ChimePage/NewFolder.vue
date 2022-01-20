<template>
  <div class="new-folder">
    <label for="createFolder" class="visually-hidden">Add Folder</label>
    <input
      id="createFolder"
      v-model="folder_name"
      type="text"
      class="form-control new-folder__input"
      name="createFolder"
      placeholder="Folder Name"
      @keyup.enter="new_folder"
    />
    <button
      data-cy="create-folder-button"
      type="button"
      class="
        new-folder__button
        btn btn-outline-primary
        align-items-center
        d-flex
      "
      @click="new_folder"
    >
      <i class="material-icons">add</i>
      Add Folder
    </button>
    <div
      v-if="chime.lti_grade_mode == 'multiple_grades'"
      class="alert alert-warning mt-2"
      role="alert"
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
<style scoped>
.new-folder {
  display: flex;
  flex-wrap: wrap;
  gap: 0.5rem;
}
.new-folder__input {
  max-width: 30rem;
}
.new-folder__button {
  flex-shrink: 0;
}
</style>

<script>
export default {
  props: ["chime"],
  data: function () {
    return {
      folder_name: "",
    };
  },
  methods: {
    filter_folder: function () {
      // NOTE Filter folder mechanic muted
      // this.$emit('filterfolder', this.folder_name);
    },
    new_folder: function () {
      this.$emit("newfolder", this.folder_name);
      this.folder_name = "";
      document.activeElement.blur();
    },
  },
};
</script>
