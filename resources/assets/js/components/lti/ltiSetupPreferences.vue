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
            name="lti_grade_mode"
            :key="choice.id"
            :img="choice.img"
            :title="choice.title"
            :description="choice.description"
            :value="choice.id"
            :isActive="gradePassbackChoice === choice.id"
            @change="(value) => (gradePassbackChoice = value)"
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
            :description="choice.description"
            :value="choice.id"
            :isActive="gradeCalcChoice === choice.id"
            name="only_correct_answers_lti"
            @change="(value) => (gradeCalcChoice = value)"
          />
        </div>
      </li>
    </ol>

    <div class="form-actions">
      <button @click.prevent="cancelForm" class="btn btn-outline-secondary">
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
<style scoped>
.setup-list {
  margin: 4rem 0;
  padding: 0;
  list-style: none;
  counter-reset: setup-list;
}

.setup-list li {
  margin: 4rem 0;
  counter-increment: setup-list;
  padding-left: 3rem;
  position: relative;
}

.setup-list li::before {
  content: counter(setup-list);
  display: flex;
  width: 2rem;
  height: 2rem;
  border-radius: 50%;
  border: 2px solid #333;
  font-weight: bold;
  align-items: center;
  justify-content: center;
  position: absolute;
  top: 0;
  left: 0;
  background: var(--gold-light);
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
  text-align: right;
}
</style>

<script>
import JumboRadio from "../JumboRadio.vue";

const PASSBACK = {
  NONE: "no_grades",
  ONE: "one_grade",
  MULTIPLE: "multiple_grades",
};

const PARTICIPATION_CREDIT = {
  FULL: "0",
  NONE: "1",
  PARTIAL: "2",
};

export default {
  components: {
    JumboRadio,
  },
  data() {
    return {
      gradePassbackChoice: null,
      gradeCalcChoice: null,
    };
  },
  computed: {
    isSubmitDisabled() {
      return [this.gradePassbackChoice, this.gradeCalcChoice].some(
        (val) => val === null
      );
    },
    gradePassbackChoices: () => [
      {
        id: PASSBACK.NONE,
        title: "No Grades",
        description: "No participation grade will be recorded in Canvas.",
        img: {
          src: "/img/passback-none.svg",
          alt: "illustration that no participation will be recorded in Canvas. From left to right: Three check marks, then an arrow pointing right with an X over the arrow, then nothing to the right of the arrow.",
        },
      },
      {
        id: PASSBACK.ONE,
        title: "One Grade",
        description:
          "One grade column for all ChimeIn assignments in Canvas, totalling all participation.",
        img: {
          src: "/img/passback-one.svg",
          alt: "Illustration that all assignment scores will be aggregated into one grade in Canvas. From left to right: Three checkmarks, then an arrow pointing right, then a single large star.",
        },
      },
      {
        id: PASSBACK.MULTIPLE,
        title: "Multiple Grades",
        description:
          "Separate grade column for each ChimeIn assignment in Canvas",
        img: {
          src: "/img/passback-many.svg",
          alt: "Illustration that each Chime In Assignment will have its own grade column. From left to right: Three checkmarks, then an arrow pointing right, then 3 stars the same size as the checkmarks.",
        },
      },
    ],
    gradeCalcChoices: () => [
      {
        id: PARTICIPATION_CREDIT.FULL,
        title: "Any Participation",
        description:
          "Full credit for participation. No credit for no response.",
        img: {
          src: "/img/participation-credit-full.svg",
          alt: "Full credit for participation. Illustration shows a checkmark worth 100%, an X worth 100%, and a blank worth 0%.",
        },
      },
      {
        id: PARTICIPATION_CREDIT.PARTIAL,
        title: "Partial Credit",
        description:
          "Full credit for correct answers. Half credit for participation. No credit for no response.",
        img: {
          src: "/img/participation-credit-partial.svg",
          alt: "Partial credit for participation. Illustration shows a checkmark worth 100%, an X worth 50%, and a blank worth 0%.",
        },
      },
      {
        id: PARTICIPATION_CREDIT.NONE,
        title: "Correct Answers Only",
        description:
          "Full credit for correct answers. Incorrect or non-responses are earn no credit.",
        img: {
          src: "/img/participation-credit-none.svg",
          alt: "No credit for participation, only correct answer. Illustration shows a checkmark worth 100%, an X worth 0%, and a blank worth 0%.",
        },
      },
    ],
  },
  methods: {
    cancelForm() {
      this.gradePassbackChoice = null;
      this.gradeCalcChoice = null;
      // window.history.go(-1);
    },
  },
};
</script>
