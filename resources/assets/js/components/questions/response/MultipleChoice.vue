<template>
    <div>

    <fieldset class="form-group" role="radiogroup">
        
        <div class="form-check"  v-bind:key="key" v-for="(option, key) in selectOptions">
            <input class="form-check-input" :disabled="disabled" type="radio" v-bind:id="'radio'+question.id + '_' + key" v-model="selected" :value="option">
            <label class="form-check-label" v-bind:for="'radio'+question.id + '_' + key" >
                {{ option }}
            </label>
        </div>
    
    </fieldset>
    <div class="form-group" v-if="question.allow_multiple && this.selected && !disabled">
        <button class="btn btn-primary" @click="clear">Clear and Start a New Response</button>
    </div>
    </div>

</template>

<script>
export default {
    props: ['question', 'response', 'disabled'],
    data() {
        return {
            selected: null,
            create_new_response: false,
        }
    },
    computed: {
        selectOptions: function() {
            return this.question.question_info.question_responses;
        }
    },
    methods: {
        clear: function() {
            this.selected = null;
            this.create_new_response = true;
        }
    },
    watch: {
        selected: function(newValue, value) {
            if(newValue !== null && newValue !== value && !(!this.create_new_response && this.response && this.response.response_info && newValue == this.response.response_info.choice)) {
                const response = {
                    question_type: 'multiple_choice',
                    choice: newValue
                }
                this.$emit('recordresponse', response, this.create_new_response);
                this.create_new_response = false;
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
