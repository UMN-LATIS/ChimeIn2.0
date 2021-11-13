<template>
  <div class="chime-export">
    <h2 class="chime-export__heading">Export</h2>
    <form method="post" :action="'/api/chime/' + chime.id + '/export'">
      <input type="hidden" name="_token" :value="csrf" />

      <div class="form-check">
        <label class="form-check-label">
          <input
            type="checkbox"
            class="form-check-input"
            name="only_correct_answers"
          />
          Only count "correct" answers (for multiple choice)
        </label>
      </div>
      <fieldset class="form-group border p-2">
        <legend class="col-form-label w-auto">Export Type</legend>
        <div class="row">
          <div class="col-sm-10">
            <div class="form-check">
              <label class="form-check-label">
                <input
                  type="radio"
                  class="form-check-input"
                  name="export_type"
                  id="export_type_folder_summary"
                  value="folder_summary"
                  checked
                />
                Folder Participation
              </label>
            </div>
            <div class="form-check">
              <label class="form-check-label">
                <input
                  type="radio"
                  class="form-check-input"
                  name="export_type"
                  id="export_type_question_summary"
                  value="question_summary"
                />
                Question Participation
              </label>
            </div>
            <div class="form-check">
              <label class="form-check-label">
                <input
                  type="radio"
                  class="form-check-input"
                  name="export_type"
                  id="export_type_question_full"
                  value="question_full"
                />
                Full Responses
              </label>
            </div>
            <div class="form-check">
              <label class="form-check-label">
                <input
                  type="radio"
                  class="form-check-input"
                  name="export_type"
                  id="export_type_question_only"
                  value="question_only"
                />
                Questions (no responses)
              </label>
            </div>
            <div class="form-check">
              <label class="form-check-label">
                <input
                  type="radio"
                  class="form-check-input"
                  name="export_type"
                  id="export_type_question_full_sessions"
                  value="question_sessions"
                />
                Individual Sessions with Responses
              </label>
            </div>
          </div>
        </div>
      </fieldset>
      <fieldset class="form-group border p-2">
        <legend class="col-form-label w-auto">Export Items</legend>
        <div class="row">
          <div class="col-sm-10">
            <div class="form-check">
              <label class="form-check-label">
                <input
                  type="radio"
                  class="form-check-input"
                  v-model="exportGroup"
                  name="export_group"
                  id="export_group_all"
                  value="all"
                  checked
                />
                All Folders
              </label>
            </div>
            <div class="form-check">
              <label class="form-check-label">
                <input
                  type="radio"
                  class="form-check-input"
                  v-model="exportGroup"
                  name="export_group"
                  id="export_group_selected"
                  value="selected"
                />
                Selected Folders
              </label>
            </div>
          </div>
        </div>
        <div class="row" v-if="exportGroup == 'selected'">
          <div class="col-sm-10 offset-sm-1">
            <div
              class="form-check"
              v-for="folder in chime.folders"
              :key="folder.id"
            >
              <label class="form-check-label">
                <input
                  type="checkbox"
                  class="form-check-input"
                  name="selectedFolder[]"
                  id=""
                  :value="folder.id"
                />
                {{ folder.name }}
              </label>
            </div>
          </div>
        </div>
      </fieldset>
      <div class="form-group row">
        <div class="col-sm-10">
          <button type="submit" class="btn btn-primary">Export</button>
        </div>
      </div>
    </form>
  </div>
</template>

<style scoped>
.chime-export {
  padding: 1rem;
  max-width: 40rem;
}
.chime-export__heading {
  font-size: 1.5rem;
  margin-bottom: 1rem;
}
</style>

<script>
export default {
  props: ["chime"],
  data: function() {
    return {
      exportGroup: "all",
      csrf: document
        .querySelector('meta[name="csrf-token"]')
        .getAttribute("content"),
    };
  },
  watch: {},
  computed: {},
  methods: {},
  mounted() {},
};
</script>
