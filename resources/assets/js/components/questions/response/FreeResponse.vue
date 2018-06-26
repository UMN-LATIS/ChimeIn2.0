<template>
    <div>
        <col>
        <b-form-textarea 
                     v-model="response_text"
                     placeholder="Type your response"
                     :rows="3"
                     :disabled="disabled"
                     :max-rows="6">
        </b-form-textarea>
        </col>
        <col>
            <b-button v-if="(!disabled && !response.id) || create_new_response" type="button" variant="primary" @click="record_response">Save</b-button>
            <b-button v-if="!disabled && response.id && !create_new_response" type="button" variant="primary" @click="record_response">Update</b-button>
            <b-button v-if="!disabled && response.id && !create_new_response" type="button" variant="primary" @click="new_response">+ New Response</b-button>
        </col>
    </div>
</template>

<script>
export default {
    props: ['question', 'response', 'disabled'],
    data() {
        return {
            response_text: "",
            create_new_response: false
        }
    },
    watch: {
        response: function(value) {
            if(this.response && this.response.response_info) {
                this.response_text = this.response.response_info.text;    
            }
            
        }
    },
    methods: {
        record_response: function() {            
            const response = {
                question_type: 'free_response',
                text: this.response_text
            }

            this.$emit('recordresponse', response, this.create_new_response);
            this.create_new_response = false;
        },
        new_response: function() {
            this.create_new_response = true;
            this.response_text = "";
        }
    },
    mounted() {
        if(this.response && this.response.hasOwnProperty('response_info') && this.response.response_info.hasOwnProperty('text')) {
            this.response_text = this.response.response_info.text;
        }
    }
};
</script>
