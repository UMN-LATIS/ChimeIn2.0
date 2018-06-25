<template>
    <div class="row questionContainer">
        <div
            class="col-12"
            v-if="question.question_info.question_type"
            v-bind:class="{in_progress: response.id}">
            
            <p class="quesiton-text" v-html="question.text"></p>
            
            <multiple-choice-question
                v-if="question.question_info.question_type === 'multiple_choice'"
                :question="question"
                :response="response"
                :disabled="false"
                v-on:recordresponse="record_response">
            </multiple-choice-question>
            <image-response-question
                v-else-if="question.question_info.question_type === 'image_response'"
                :question="question"
                :response="response"
                :disabled="false"
                v-on:recordresponse="record_response">
            </image-response-question>
            <free-response-question
                v-else-if="question.question_info.question_type === 'free_response'"
                :question="question"
                :response="response"
                :disabled="false"
                v-on:recordresponse="record_response"
            ></free-response-question>
        </div>
        <!-- <div class="card-content" v-else>
            <span class="card-title">
                <h4>'No Question Yet!'</h4>
            </span>
        </div> -->
    </div>
</template>

<style>

.questionContainer {
    border: 1px solid black;
    border-radius: 5px;
    margin: 5px;
}

</style>

<script>
export default {
    props: ['session', 'chime', 'responses'],
    data: function() {
        return {
            question: {
                question_info: ''
            },
        }
    },
    computed: {
        response: function(){
            if(this.responses.length > 0 && this.session) {
                var foundResponse;
                this.responses.forEach(response=> {
                    if(response.session_id == this.session.id) {
                        foundResponse = response
                    }
                });  
                return foundResponse;  
            }
            
            return {};
        }
    },
    methods: {
        record_response: function(response) {
            const self = this;

            var url = '/api/chime/'
                + this.chime.id
                + '/session/'
                + this.session.id
                + '/response';

            if (this.response.id) {
                url = url + "/" + this.response.id
            }

            axios.put(url, {'response_info': response})
            .then(res => {
                console.log('debug', 'response recorded:', res);
                this.$emit('updateResponse', res.data);
            })
            .catch(err => {
                console.error(
                    'error', 'error recording response', err.response);
            });
            document.activeElement.blur();
        }
    },
    created: function() {
        this.question = this.session.question;
    }
};
</script>

<style>
    li {
        font-size: 1.5em;
    }

    .col.s12 > .btn {
        width: 100%;
    }
</style>