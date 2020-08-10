<template>
    <div>
        <div class="row value-slider">

                <div class="col-1"> {{ left_choice_text}}</div>
                 <div class="col-10">
                <div class="range-wrap ">
                <input type="range" :disabled="disabled" class="form-control-range custom-range range" :value="sliderValue" @change="valueChanged($event.target.value)" id="formControlRange" >
                <output class="bubble" :style="customStyle" v-if="question.question_info.question_responses.range_type == 'Numeric (Linear)'">{{ bubbleValue }}</output>
                </div>
                </div>
                <div class="col-1">{{ right_choice_text}}</div>

        </div>
      
         <div class="form-group" v-if="question.allow_multiple && !disabled">
            <button class="btn btn-primary" @click="clear">Clear and Start a New Response</button>
        </div>
    </div>
</template>
<style scoped>

  .range-wrap {
        position: relative;
        margin: 0 auto 3rem;
    }

    .range {
        width: 100%;
    }

    .bubble {
        font-size: 2em;
    }
    .value-slider {
        font-size: 1.3em;
    }

    .bubble {
        display: inline;
        border: 1px solid #7a0019;
        border-radius: 4px;
        color: black;
        padding: 7px;
        position: absolute;
        left: 50%;
        transform: translateX(-50%);
        top: 23px;
        /* transform: translateX(-60%); */
        background-color: white;
    }

    .bubble::after {
        content: "";
        position: absolute;
        width: 2px;
        height: 3px;
        background: #7a0019;
        top: -3px;
        left: 50%;
    }



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
            create_new_response: false,
                customStyle: {
                    left: "0px",
                    right: "0px"
                },
                bubbleValue: "",
                bubbleOffset: 0
        }
    },
    methods: {
        valueChanged: function(targetValue) {
            this.updateRange(targetValue);
            const response = {
                question_type: 'slider',
                choice: targetValue
            }
            this.$emit('recordresponse', response, this.create_new_response);
            this.create_new_response = false;

        },
        clear: function() {
            this.create_new_response = true;
        },
        updateRange: function (newValue) {

                let range = parseInt(this.right_choice_text) - parseInt(this.left_choice_text);
                this.bubbleValue =parseInt(this.left_choice_text) +(range * newValue/100);
                var computed = newValue;
                var otherValue = 7 - computed * 0.15;
                this.customStyle ={
                    left: `calc(${computed}% + (${otherValue}px))`
                };
            }
    },
    watch: {

    },
    computed: {
        sliderValue: function() {
            if(this.create_new_response) {
                return 50;
            }
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
        this.updateRange(this.sliderValue);
    }
}
</script>
