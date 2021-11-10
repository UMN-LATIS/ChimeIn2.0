<template>
    <ul>
        <li>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="joinInstructions" id="joinInstructions"
                    v-model="join_instructions_local" @change="$emit('update:join_instructions', $event.target.checked)">
                <label class="form-check-label" for="joinInstructions">Display "join" instructions when
                    presenting</label>
            </div>
        </li>
        <li>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="requireLogin" id="requireLogin" v-model="require_login_local"
                    @change="$emit('update:require_login', $event.target.checked);">
                <label class="form-check-label" for="requireLogin">Require Login to Join or Access</label>
            </div>
        </li>
        <li>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="studentView" id="studentView" v-model="students_can_view_local"
                    @change="$emit('update:students_can_view', $event.target.checked)">
                <label class="form-check-label" for="studentView">Participants can view results</label>
            </div>
        </li>
        <li>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="showFolderTitle" id="showFolderTitle"
                    v-model="show_folder_title_to_participants_local" @change="$emit('update:show_folder_title_to_participants', $event.target.checked)">
                <label class="form-check-label" for="showFolderTitle">Reveal folder titles to participants</label>
            </div>
        </li>
        <li v-if="!new_chime">
             <fieldset class="form-group border p-2 pt-0 mt-2">
                <legend class="col-form-label w-auto mb-0 pb-0">Grading Options (for Canvas-linked Chimes)</legend>
            <div class="form-check">
                <label class="form-check-label">
                <input type="radio" class="form-check-input" name="onlyCorrectAnswersLti" id="onlyCorrectAnswersLti_any" value="0"  v-model="only_correct_answers_lti_local" @change="$emit('update:only_correct_answers_lti', $event.target.value)">
                Count any participation
              </label>
            </div>
            <div class="form-check">
                <label class="form-check-label">
                <input type="radio" class="form-check-input" name="onlyCorrectAnswersLti" id="onlyCorrectAnswersLti_participation" value="2"  v-model="only_correct_answers_lti_local" @change="$emit('update:only_correct_answers_lti', $event.target.value)">
                Half credit for participation, full credit for "correct" answers (for questions which have a "correct" response)
              </label>
            </div>
            <div class="form-check">
                <label class="form-check-label">
                <input type="radio" class="form-check-input" name="onlyCorrectAnswersLti" id="onlyCorrectAnswersLti_correct" value="1"  v-model="only_correct_answers_lti_local" @change="$emit('update:only_correct_answers_lti', $event.target.value)">
                Only count "correct" answers (for questions which have a "correct" response)
              </label>
            </div>
            </fieldset>
        </li>
    </ul>
</template>

<style scoped>
ul li {
  list-style: none;
}
</style>

<script>

export default {
        data() {
            return {
                require_login_local: this.require_login,
                students_can_view_local: this.students_can_view,
                join_instructions_local: this.join_instructions,
                only_correct_answers_lti_local: this.only_correct_answers_lti,
                show_folder_title_to_participants_local: this.show_folder_title_to_participants,
            }
        },
        props: ['require_login','students_can_view', 'join_instructions', 'only_correct_answers_lti','new_chime', 'show_folder_title_to_participants'],

}
</script>