<template>
        <b-form-group  label="Stacked radios" >
            <b-form-radio-group :disabled="disabled" v-model="selected" :options="selectOptions"
                          stacked
                          >
            </b-form-radio-group>
        </b-form-group>
</template>

<script>
export default {
    props: ['question', 'response', 'disabled'],
    data() {
        return {
            selected: null,
        }
    },
    computed: {
        selectOptions: function() {
            return this.question.question_info.question_responses;
        }
    },
    watch: {
        selected: function(value) {
            const response = {
                question_type: 'multiple_choice',
                choice: value
            }
            this.$emit('recordresponse', response);
        },
        response: function(value) {
            if(this.response.response_info) {
                this.selected = this.response.response_info.choice;    
            }
            
        }
    },
    mounted() {
        if(this.response.hasOwnProperty('response_info') && this.response.response_info.hasOwnProperty('choice')) {
            this.selected = this.response.response_info.choice;
        }
    }
}
</script>
