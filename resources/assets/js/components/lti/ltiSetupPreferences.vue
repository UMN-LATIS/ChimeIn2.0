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

        <div class="jumbo-button-group" @update="handleUpdate">
          <JumboButton
            v-for="choice in gradeTypeChoices"
            :key="choice.id"
            :img="choice.img"
            :title="choice.title"
            :description="choice.description"
            :value="choice.id"
          />
        </div>
      </li>
    </ol>
  </div>
</template>
<style scoped>
.setup-list {
  margin: 2rem 0;
  padding: 0;
  list-style: none;
  counter-reset: setup-list;
}

.setup-list li {
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
  top: 0.25rem;
  left: 0;
  background: var(--gold-light);
}

.setup-item__heading {
    margin-bottom: 1.5rem;
}

.jumbo-button-group {
  display: grid;
  grid-template-columns: repeat(3, minmax(0, 1fr));
  gap: 1rem;
}

@media (max-width: 40rem) {
    .jumbo-button-group {
        grid-template-columns: minmax(0, 1fr);
    }
}
</style>

<script>
import JumboButton from "../JumboButton.vue";

export default {
  components: {
    JumboButton,
  },
  data() {
    return {
      gradeTypeChoice: null,
      gradeCalcChoice: null,
    };
  },
  methods: {
    handleUpdate(x) {
      console.log(x);
    },
  },
  computed: {
    gradeTypeChoices: () => [
      {
        id: "no_grades",
        title: "No Grades",
        description: "No participation grade will be recorded in Canvas.",
        img: {
          src: "/img/passback-none.svg",
          alt:
            "illustration that no participation will be recorded in Canvas. From left to right: Three check marks, then an arrow pointing right with an X over the arrow, then nothing to the right of the arrow.",
        },
      },
      {
        id: "one_grade",
        title: "One Grade",
        description:
          "One grade column for all Chimeln assignments in Canvas, totalling all participation.",
        img: {
          src: "/img/passback-one.svg",
          alt:
            "Illustration that all assignment scores will be aggregated into one grade in Canvas. From left to right: Three checkmarks, then an arrow pointing right, then a single large star.",
        },
      },
      {
        id: "multiple_grades",
        title: "Multiple Grades",
        description:
          "Separate grade column for each Chimeln assignment in Canvas",
        img: {
          src: "/img/passback-many.svg",
          alt:
            "Illustration that each Chime In Assignment will have its own grade column. From left to right: Three checkmarks, then an arrow pointing right, then 3 stars the same size as the checkmarks.",
        },
      },
    ],
    gradeCalcChoices: () => [
      {
        id: "0",
        label: "Any participation",
        description: "Any response counts towards the grade in the gradebook.",
      },
      {
        id: "1",
        label: '"Correct" answers',
        description:
          'Multiple Choice questions in ChimeIn can have a "correct" answer marked. When this option is selected, students responding to multiple choice questions will only recieve credit for correct answers. Any participation in other types of questions (free response, heatmap, etc) counts towards the grade.',
      },
      {
        id: "2",
        label: "Partial credit",
        description:
          'Half credit for participation, full credit for "correct" answers',
      },
    ],
  },
};
</script>
