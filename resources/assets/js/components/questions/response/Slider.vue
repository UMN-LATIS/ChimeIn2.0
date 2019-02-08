<template>
    <div>
        <div class="row">
            <div class="form-group col">
                <input type="range" :disabled="disabled" class="form-control-range custom-range" :value="sliderValue" @change="valueChanged($event.target.value)" id="formControlRange">
            </div>
        </div>
        <div class="row justify-content-between">
            <div class="col-sm-2">
                {{ left_choice_text}}
            </div>
            <div class="col-sm-2 text-right">
                {{ right_choice_text}}
            </div>
        </div>
    </div>
</template>
<style>

.custom-range:disabled::-webkit-slider-thumb {
  background-color: lightgray;
}

.custom-range:disabled::-moz-range-thumb {
  background-color: lightgray;
}

.custom-range:disabled::-ms-thumb {
  background-color: lightgray;
}

</style>

<script>
export default {
    props: ['question', 'response', 'disabled'],
    data() {
        return {
        }
    },
    methods: {
        valueChanged: function(targetValue) {
            const response = {
                question_type: 'slider',
                choice: targetValue
            }
            this.$emit('recordresponse', response);
        }
    },
    computed: {
        sliderValue: function() {
            if(this.response && this.response.response_info) {
                return this.response.response_info.choice;
            }
        },
        left_choice_text: function() {
            if(this.question && "left_choice_text" in this.question.question_info.question_responses) {
                return this.question.question_info.question_responses.left_choice_text
            }

        },
        right_choice_text: function() {
            if(this.question && "right_choice_text" in this.question.question_info.question_responses) {
                return this.question.question_info.question_responses.right_choice_text
            }

        }
    },
    mounted() {
    }
}
</script>
