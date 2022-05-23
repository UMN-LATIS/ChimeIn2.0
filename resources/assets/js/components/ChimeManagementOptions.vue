<template>
  <ul>
    <li>
      <div class="form-check">
        <input
          id="joinInstructions"
          :checked="join_instructions"
          class="form-check-input"
          type="checkbox"
          name="joinInstructions"
          @change="handleChange(
            'join_instructions', 
            ($event.target as HTMLInputElement).checked
            )"
        />
        <label class="form-check-label" for="joinInstructions"
          >Display "join" instructions when presenting</label
        >
      </div>
    </li>
    <li>
      <div class="form-check">
        <input
          id="requireLogin"
          class="form-check-input"
          type="checkbox"
          name="requireLogin"
          :checked="require_login"
          @change="handleChange(
            'require_login', 
            ($event.target as HTMLInputElement).checked
            )"
        />
        <label class="form-check-label" for="requireLogin"
          >Require Login to Join or Access</label
        >
      </div>
    </li>
    <li>
      <div class="form-check">
        <input
          id="studentView"
          class="form-check-input"
          type="checkbox"
          name="studentView"
          :checked="students_can_view"
          @change="handleChange(
            'students_can_view', 
            ($event.target as HTMLInputElement).checked
            )"
        />
        <label class="form-check-label" for="studentView"
          >Participants can view results</label
        >
      </div>
    </li>
    <li>
      <div class="form-check">
        <input
          id="showFolderTitle"
          class="form-check-input"
          type="checkbox"
          name="showFolderTitle"
          :checked="show_folder_title_to_participants"
          @change="handleChange(
            'show_folder_title_to_participants', 
            ($event.target as HTMLInputElement).checked
            )"
        />
        <label class="form-check-label" for="showFolderTitle"
          >Reveal folder titles to participants</label
        >
      </div>
    </li>
    <li v-if="!new_chime">
      <fieldset class="form-group border p-2 pt-0 mt-2">
        <legend class="col-form-label w-auto mb-0 pb-0">
          Grading Options (for Canvas-linked Chimes)
        </legend>
        <div
          v-for="gradeOption in gradeOptions"
          :key="gradeOption.value"
          class="form-check"
        >
          <label class="form-check-label">
            <input
              type="radio"
              class="form-check-input"
              name="onlyCorrectAnswersLti"
              value="gradeOption.value"
              @change="handleChange('only_correct_answers_lti', 
            ($event.target as HTMLInputElement).value)"
            />
            {{ gradeOption.description }}
          </label>
        </div>
      </fieldset>
    </li>
  </ul>
</template>

<script setup lang="ts">
/* eslint-disable vue/prop-name-casing */
import { LTIGradeOptions, Partial, ChimeOptions } from "../types";

interface Props {
  new_chime?: boolean;
  require_login: boolean;
  /** students can view response results */
  students_can_view: boolean;
  /** show join instructions */
  join_instructions: boolean;
  only_correct_answers_lti: LTIGradeOptions;
  show_folder_title_to_participants: boolean;
}

defineProps<Props>();

interface Emits {
  (event: "update", payload: Partial<ChimeOptions>);
}

const emit = defineEmits<Emits>();

function handleChange(propName: keyof ChimeOptions, value) {
  emit("update", {
    [propName]: value,
  });
}

const gradeOptions = [
  {
    value: LTIGradeOptions.FULL_CREDIT_FOR_PARITICIPATION,
    description: "Count any participation",
  },
  {
    value: LTIGradeOptions.HALF_CREDIT_FOR_PARTICIPATION,
    description:
      'Half credit for participation, full credit for "correct" answers (for questions which have a "correct" response)',
  },
  {
    value: LTIGradeOptions.ONLY_POINTS_FOR_CORRECT,
    description:
      'Only count "correct" answers (for questions which have a "correct" response)',
  },
];
</script>

<style scoped>
ul li {
  list-style: none;
}
</style>
