<template>
  <div class="chime-export">
    <h2 class="chime-export__heading">Export</h2>
    <form method="post" :action="'/api/chime/' + chime.id + '/export'">
      <input type="hidden" name="_token" :value="csrf" />
      <fieldset class="form-group border p-2">
        <legend class="col-form-label w-auto">Export Grading</legend>
        <div class="form-check">
          <label class="form-check-label">
            <input
              type="radio"
              class="form-check-input"
              name="only_correct_answers"
              value="0"
              checked
            />
            Count all participation
          </label>
        </div>
        <div class="form-check">
          <label class="form-check-label">
            <input
              type="radio"
              class="form-check-input"
              name="only_correct_answers"
              value="1"
            />
            Only count "correct" answers (for multiple choice)
          </label>
        </div>
        <div class="form-check">
          <label class="form-check-label">
            <input
              type="radio"
              class="form-check-input"
              name="only_correct_answers"
              value="2"
            />
            Half credit for participation, full credit for correct answers
          </label>
        </div>
      </fieldset>

      <fieldset class="form-group border p-2">
        <legend class="col-form-label w-auto">Export Type</legend>
        <div class="row">
          <div class="col-sm-10">
            <div class="form-check">
              <label class="form-check-label">
                <input
                  id="export_type_folder_summary"
                  type="radio"
                  class="form-check-input"
                  name="export_type"
                  value="folder_summary"
                  checked
                />
                Folder Participation
              </label>
            </div>
            <div class="form-check">
              <label class="form-check-label">
                <input
                  id="export_type_question_summary"
                  type="radio"
                  class="form-check-input"
                  name="export_type"
                  value="question_summary"
                />
                Question Participation
              </label>
            </div>
            <div class="form-check">
              <label class="form-check-label">
                <input
                  id="export_type_question_full"
                  type="radio"
                  class="form-check-input"
                  name="export_type"
                  value="question_full"
                />
                Full Responses
              </label>
            </div>
            <div class="form-check">
              <label class="form-check-label">
                <input
                  id="export_type_question_only"
                  type="radio"
                  class="form-check-input"
                  name="export_type"
                  value="question_only"
                />
                Questions (no responses)
              </label>
            </div>
            <div class="form-check">
              <label class="form-check-label">
                <input
                  id="export_type_question_full_sessions"
                  type="radio"
                  class="form-check-input"
                  name="export_type"
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
                  id="export_group_all"
                  v-model="exportGroup"
                  type="radio"
                  class="form-check-input"
                  name="export_group"
                  value="all"
                  checked
                />
                All Folders
              </label>
            </div>
            <div class="form-check">
              <label class="form-check-label">
                <input
                  id="export_group_selected"
                  v-model="exportGroup"
                  type="radio"
                  class="form-check-input"
                  name="export_group"
                  value="selected"
                />
                Selected Folders
              </label>
            </div>
          </div>
        </div>
        <div v-if="exportGroup == 'selected'" class="row">
          <div class="col-sm-10 offset-sm-1">
            <div
              v-for="folder in chime.folders"
              :key="folder.id"
              class="form-check"
            >
              <label class="form-check-label">
                <input
                  id=""
                  type="checkbox"
                  class="form-check-input"
                  name="selectedFolder[]"
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
  data: function () {
    return {
      exportGroup: "all",
      csrf: document
        .querySelector('meta[name="csrf-token"]')
        .getAttribute("content"),
    };
  },
  computed: {},
  watch: {},
  mounted() {},
  methods: {},
};
</script>
