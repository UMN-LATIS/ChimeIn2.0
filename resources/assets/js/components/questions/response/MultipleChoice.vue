<template>
    <div>

    <fieldset class="form-group" role="radiogroup">
        
        <div class="form-check"  v-bind:key="key" v-for="(option, key) in selectOptions">
            <input class="form-check-input" :disabled="disabled" v-bind:type="question.allow_multiple?'checkbox':'radio'" v-bind:id="'radio'+question.id + '_' + key" v-model="selected" :value="option">
            <label class="form-check-label" v-bind:for="'radio'+question.id + '_' + key" >
                {{ isObject(option)?option.text:option }}
            </label>
        </div>
    
    </fieldset>
    </div>

</template>

<script>
export default {
    props: ['question', 'response', 'disabled'],
    data() {
        return {
            selected: []
        }
    },
    computed: {
        selectOptions: function() {
            return this.question.question_info.question_responses;
        }
    },
    watch: {
        selected: function(newValue, value) {
            if(newValue !== null && newValue !== value && !(this.response && this.response.response_info && newValue == this.response.response_info.choice)) {
                const response = {
                    question_type: 'multiple_choice',
                    choice: newValue
                }
                this.$emit('recordresponse', response, false);
            }
        },
        response: function(value) {
            if(this.response && this.response.response_info) {
                this.selected = this.response.response_info.choice;
            }
            
        }
    },
    mounted() {
        if(this.response && this.response.hasOwnProperty('response_info') && this.response.response_info.hasOwnProperty('choice')) {
            this.selected = this.response.response_info.choice;
        }
    }
}
</script>
