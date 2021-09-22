<template>
    <div>
        <p>
            How would you like to use ChimeIn with this course? If you're not sure about these options, visit our <a href="https://umn-latis.github.io/ChimeIn2.0/canvas.html" target="_blank">help page</a>.
        </p>

        <h2>Grade Setup</h2>
        <div class="form-check" v-for="gradeType in gradeTypes" :key="gradeType.id">
          <label class="form-check-label">
            <input type="radio" class="form-check-input" name="lti_grade_mode" :id="gradeType.id" :value="gradeType.id" :checked="gradeType.checked">
            <b>{{ gradeType.label}}</b>
            {{ gradeType.description }}
          </label>
        </div>

         <h2>Grading Calculation</h2>
        <div class="form-check" v-for="gradeCalc in gradeCalcs" :key="gradeCalc.id">
          <label class="form-check-label">
            <input type="radio" class="form-check-input" name="only_correct_answers_lti" :id="gradeCalc.id" :value="gradeCalc.id" :checked="gradeCalc.checked">
            <b>{{ gradeCalc.label}}</b>
            {{ gradeCalc.description }}
          </label>
        </div>
        <button type="submit" class="btn btn-primary">Create Chime</button>
    </div>
</template>

<script>
export default {
    data() {
        return {
            gradeTypes: [
                {
                    "id": "no_grades",
                    "label": "No grades",
                    "description": "ChimeIn won't pass any data back to Canvas. If needed, you can still access participation data within ChimeIn",
                    "checked": false
                },
                {
                    "id": "one_grade",
                    "label": "One Grade",
                    "description": "This will allow you to separately track grades for each week/lecture/etc. You'll create multiple assignments in Canvas, and manage your points in Canvas.",
                    "checked": false
                },
                {
                    "id": "multiple_grades",
                    "label": "Multiple Grades",
                    "description": "The Canvas gradebook entry will be based on the total number of questions students respond to across all of your folders. You can still create folders in ChimeIn to organize your questions. ",
                    "checked": true
                }
            ],
            gradeCalcs: [
                {
                    "id": "0",
                    "label": 'Any participation',
                    "description": "Any response counts towards the grade in the gradebook.",
                    "checked": true
                },
                {
                    "id": "1",
                    "label": '"Correct" answers',
                    "description": 'Multiple Choice questions in ChimeIn can have a "correct" answer marked. When this option is selected, students responding to multiple choice questions will only recieve credit for correct answers. Any participation in other types of questions (free response, heatmap, etc) counts towards the grade.',
                    "checked": false
                },
                {
                    "id": "2",
                    "label": 'Partial credit',
                    "description": 'Half credit for participation, full credit for "correct" answers',
                    "checked": false
                }
            ]
        }
    }
}
</script>