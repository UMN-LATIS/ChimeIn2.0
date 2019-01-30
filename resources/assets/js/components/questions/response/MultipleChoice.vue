<template>
    <div class="form-group">
        <div class="form-check" v-for="(option, key) in selectOptions">
            <input class="form-check-input" :disabled="disabled" type="radio" v-bind:id="'radio'+question.id + '_' + key" v-model="selected" :value="option">
            <label class="form-check-label" v-bind:for="'radio'+question.id + '_' + key" >
                {{ option }}
            </label>
        </div>
    </div>

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
        selected: function(newValue, value) {
            if(newValue !== value && !(this.response && this.response.response_info && newValue == this.response.response_info.choice)) {
                const response = {
                    question_type: 'multiple_choice',
                    choice: newValue
                }
                this.$emit('recordresponse', response);
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
