<template>
  <div class="lti-setup-page">
    <p>
      How would you like to use ChimeIn with this course? If you're not sure
      about these options, visit our
      <a
        href="https://umn-latis.github.io/ChimeIn2.0/canvas.html"
        target="_blank"
        >help page</a
      >.
    </p>

    <ol class="setup-list">
      <li class="setup-item">
        <h2 class="setup-item__heading">
          How should ChimeIn assignments be recorded in Canvas?
        </h2>

        <div class="jumbo-radio-group">
          <JumboRadio
            v-for="choice in gradePassbackChoices"
            :key="choice.id"
            name="lti_grade_mode"
            :img="choice.img"
            :title="choice.title"
            :subtitle="choice.id === DEFAULT_PASSBACK ? '(Default)' : ''"
            :description="choice.description"
            :value="choice.id"
            :isActive="gradePassbackChoice === choice.id"
            @change="(value) => (gradePassbackChoice = value as PassbackType)"
          />
        </div>
      </li>

      <li class="setup-item">
        <h2 class="setup-item__heading">What participation earns points?</h2>

        <div class="jumbo-radio-group">
          <JumboRadio
            v-for="choice in gradeCalcChoices"
            :key="choice.id"
            :img="choice.img"
            :title="choice.title"
            :subtitle="
              choice.id === DEFAULT_PARTICIPATION_CREDIT ? '(Default)' : ''
            "
            :description="choice.description"
            :value="choice.id"
            :isActive="gradeCalcChoice === choice.id"
            name="only_correct_answers_lti"
            @change="
              (value) => (gradeCalcChoice = value as ParticipationCreditType)
            "
          />
        </div>
      </li>
    </ol>

    <div class="form-actions">
      <button class="btn btn-outline-secondary" @click.prevent="resetForm">
        Cancel
      </button>
      <button
        type="submit"
        :disabled="isSubmitDisabled"
        class="btn btn-primary"
      >
        Save
      </button>
    </div>
  </div>
</template>
<script setup lang="ts">
import { ref, computed } from "vue";
import JumboRadio from "../JumboRadio.vue";

const PASSBACK = {
  NO_GRADE_COLUMNS: "no_grades",
  ONE_GRADE_COLUMN: "one_grade",
  MULTIPLE_GRADE_COLUMNS: "multiple_grades",
} as const;

const PARTICIPATION_CREDIT = {
  FULL_CREDIT_FOR_INCORRECT: "0",
  NO_CREDIT_FOR_INCORRECT: "1",
  HALF_CREDIT_FOR_INCORRECT: "2",
};

const DEFAULT_PASSBACK = PASSBACK.MULTIPLE_GRADE_COLUMNS;
const DEFAULT_PARTICIPATION_CREDIT =
  PARTICIPATION_CREDIT.FULL_CREDIT_FOR_INCORRECT;

type PassbackType = (typeof PASSBACK)[keyof typeof PASSBACK];
type ParticipationCreditType =
  (typeof PARTICIPATION_CREDIT)[keyof typeof PARTICIPATION_CREDIT];

const gradePassbackChoice = ref<PassbackType>(PASSBACK.MULTIPLE_GRADE_COLUMNS);
const gradeCalcChoice = ref<ParticipationCreditType>(
  DEFAULT_PARTICIPATION_CREDIT,
);

const isSubmitDisabled = computed(() => {
  return [gradePassbackChoice.value, gradeCalcChoice.value].some(
    (val) => val === null,
  );
});

const gradePassbackChoices = [
  {
    id: PASSBACK.MULTIPLE_GRADE_COLUMNS,
    title: "Multiple Grade Columns",
    description: "Separate grade column for each ChimeIn assignment in Canvas",
    img: {
      src: "/images/passback-mult-cols.svg",
      alt: "multiple columns",
    },
  },
  {
    id: PASSBACK.ONE_GRADE_COLUMN,
    title: "One Grade Column",
    description:
      "One aggregated grade column for all ChimeIn assignments in Canvas, totalling all participation.",
    img: {
      src: "/images/passback-one-col.svg",
      alt: "one column",
    },
  },
  {
    id: PASSBACK.NO_GRADE_COLUMNS,
    title: "No Grades",
    description: "No participation grade will be recorded in Canvas.",
    img: {
      src: "/images/passback-no-cols.svg",
      alt: "No columns",
    },
  },
];

const gradeCalcChoices = [
  {
    id: PARTICIPATION_CREDIT.FULL_CREDIT_FOR_INCORRECT,
    title: "Any Participation",
    description: "Full credit for participation. No credit for no response.",
    img: {
      src: "/images/participation-credit-full.svg",
      alt: "Full credit for participation. Illustration shows a checkmark worth 100%, an X worth 100%, and a blank worth 0%.",
    },
  },
  {
    id: PARTICIPATION_CREDIT.HALF_CREDIT_FOR_INCORRECT,
    title: "Partial Credit",
    description:
      "Full credit for correct answers. Half credit for participation. No credit for no response.",
    img: {
      src: "/images/participation-credit-partial.svg",
      alt: "Partial credit for participation. Illustration shows a checkmark worth 100%, an X worth 50%, and a blank worth 0%.",
    },
  },
  {
    id: PARTICIPATION_CREDIT.NO_CREDIT_FOR_INCORRECT,
    title: "Correct Answers Only",
    description:
      "Full credit for correct answers. Incorrect or non-responses are earn no credit.",
    img: {
      src: "/images/participation-credit-none.svg",
      alt: "No credit for participation, only correct answer. Illustration shows a checkmark worth 100%, an X worth 0%, and a blank worth 0%.",
    },
  },
];

function resetForm() {
  gradePassbackChoice.value = DEFAULT_PASSBACK;
  gradeCalcChoice.value = DEFAULT_PARTICIPATION_CREDIT;
}
</script>

<style scoped>
.setup-list {
  margin: 2rem 0;
  padding: 0;
  list-style: none;
  counter-reset: setup-list;
}

.setup-list li {
  margin: 2rem 0;
  position: relative;
}

.setup-item__heading {
  font-size: 1.5rem;
  margin-bottom: 1.5rem;
  font-weight: bold;
}

.jumbo-radio-group {
  display: grid;
  grid-template-columns: repeat(3, minmax(0, 1fr));
  gap: 1rem;
}

@media (max-width: 40rem) {
  .jumbo-radio-group {
    grid-template-columns: minmax(0, 1fr);
  }
}

.form-actions {
  display: flex;
  justify-content: flex-end;
  gap: 0.5rem;
}
</style>
