<template>
    <div>
        <div class="row">
            <div class="form-group col">
                <input type="range" :disabled="disabled" class="form-control-range" :value="sliderValue" @change="valueChanged($event.target.value)" id="formControlRange">
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
input[type=range] {
  height: 25px;
  -webkit-appearance: none;
  margin: 10px 0;
  width: 100%;
}
input[type=range]:focus {
  outline: none;
}
input[type=range]::-webkit-slider-runnable-track {
  width: 100%;
  height: 5px;
  cursor: pointer;
  animate: 0.2s;
  box-shadow: 0px 0px 0px #000000;
  background: #2497E3;
  border-radius: 1px;
  border: 0px solid #000000;
}
input[type=range]::-webkit-slider-thumb {
  box-shadow: 0px 0px 0px #000000;
  border: 1px solid #2497E3;
  height: 18px;
  width: 18px;
  border-radius: 25px;
  background: #F7FCFF;
  cursor: pointer;
  -webkit-appearance: none;
  margin-top: -7px;
}
input[type=range]:focus::-webkit-slider-runnable-track {
  background: #2497E3;
}
input[type=range]::-moz-range-track {
  width: 100%;
  height: 5px;
  cursor: pointer;
  animate: 0.2s;
  box-shadow: 0px 0px 0px #000000;
  background: #2497E3;
  border-radius: 1px;
  border: 0px solid #000000;
}
input[type=range]::-moz-range-thumb {
  box-shadow: 0px 0px 0px #000000;
  border: 1px solid #2497E3;
  height: 18px;
  width: 18px;
  border-radius: 25px;
  background: #F7FCFF;
  cursor: pointer;
}
input[type=range]::-ms-track {
  width: 100%;
  height: 5px;
  cursor: pointer;
  animate: 0.2s;
  background: transparent;
  border-color: transparent;
  color: transparent;
}
input[type=range]::-ms-fill-lower {
  background: #2497E3;
  border: 0px solid #000000;
  border-radius: 2px;
  box-shadow: 0px 0px 0px #000000;
}
input[type=range]::-ms-fill-upper {
  background: #2497E3;
  border: 0px solid #000000;
  border-radius: 2px;
  box-shadow: 0px 0px 0px #000000;
}
input[type=range]::-ms-thumb {
  margin-top: 1px;
  box-shadow: 0px 0px 0px #000000;
  border: 1px solid #2497E3;
  height: 18px;
  width: 18px;
  border-radius: 25px;
  background: #F7FCFF;
  cursor: pointer;
}
input[type=range]:focus::-ms-fill-lower {
  background: #2497E3;
}
input[type=range]:focus::-ms-fill-upper {
  background: #2497E3;
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
