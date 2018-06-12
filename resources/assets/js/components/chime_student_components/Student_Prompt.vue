<template>
    <div class="card hoverable">
        <div
            class="card-content"
            v-if="question.question_info.question_type"
            v-bind:class="{in_progress: response.id}">
            <div class="card">
                <div class="card-content">
                    <p class="flow-text" v-html="question.text"></p>
                </div>
            </div>
            
            <multiple-choice-question
                v-if="question.question_info.question_type === 'multiple_choice'"
                :question="question"
                :response="response"
                v-on:recordresponse="record_response">
            </multiple-choice-question>
            <image-response-question
                v-else-if="question.question_info.question_type === 'image_response'"
                :question="question"
                :response="response"
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

<script>
export default {
    props: ['session'],
    data: function() {
        return {
            question: {
                question_info: ''
            },
            response: {}
        }
    },
    methods: {
        record_response: function(response) {
            const self = this;

            if (this.response.id) {
                const url = (
                    '/api/chime/'
                    + this.session.chime_id
                    + '/session/'
                    + this.session.id
                    + '/response/'
                    + this.response.id
                );

                axios.put(url, {'response_info': response})
                .then(res => {
                    console.log('debug', 'response recorded:', res);
                    res.data.response_info = JSON.parse(res.data.response_info);
                    self.response = res.data;
                    console.log('debug', 'edited reponse:', self.response);
                })
                .catch(err => {
                    console.error(
                        'error', 'error recording response', err.response);
                });
            } else {
                const url = (
                    '/api/chime/'
                    + this.session.chime_id
                    + '/session/'
                    + this.session.id
                );

                axios.post(url, {'response_info': response})
                .then(res => {
                    console.log('debug', 'response recorded:', res);
                    res.data.response_info = JSON.parse(res.data.response_info);
                    self.response = res.data;
                    console.log('debug', 'new reponse:', self.response);
                })
                .catch(err => {
                    console.log(err.response);
                });
            }

            document.activeElement.blur();
        }
    },
    created: function() {
        const self = this;
        const url = (
            '/api/chime/'
            + this.session.chime_id
            + '/session/'
            + this.session.id
            + '/question'
        );

        axios.get(url)
        .then(res => {
            console.log('debug', 'question:', res);
            self.question = res.data;
        })
        .catch(err => {
            console.error('error getting question:', err.response);
        });

        axios.get(
            '/api/chime/'
            + this.session.chime_id
            + '/session/'
            + this.session.id)
        .then(res => {
            self.response = res.data;
        })
        .catch(err => {
            console.error('error getting response:', err.response);
        })
    }
}
</script>

<style>
    li {
        font-size: 1.5em;
    }

    .col.s12 > .btn {
        width: 100%;
    }
</style>