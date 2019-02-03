<template>
    <li>
        <div class="row">
            <div class="col-sm-9">
                <question-form :show="show_edit" @edited="edit_question" @close="show_edit = false;"
                :question="question"
                :folder="folder"
                controlType="edit">
            </question-form>
            <div>
                <p class="response_label">{{ total_responses }}</p>
                <p class="flow-text" v-html="question.text"></p>
            </div>
        </div>
        <div class="col-sm-3">
            <div class="float-right">
                <p-check name="check" class="p-switch p-outline" color="success" v-model="check"> &nbsp;
                </p-check>
                <router-link :to="{ name: 'present', params: {chimeId: folder.chime_id, folderId: folder.id, questionId:question.order - 1} }" class="text-dark">
                <i class="material-icons">play_arrow</i>
                </router-link>
            <a class="pointer" @click="show_edit = !show_edit">
                <i class="material-icons">edit</i>
            </a>
            <a class="pointer" @click="delete_question">
                <i class="material-icons ">delete</i>
            </a>
</div>
</div>

</div>
</li>
</template>

<script>
    const QuestionForm = () => import(
        /* webpackChunkName: "QuestionForm" */
        './QuestionForm.vue'
    );

    export default {
        props: ['folder', 'question'],
        components: {
            'question-form': QuestionForm
        },
        data: function() {
            return {
                show_edit: false,
            }
        },
        methods: {
            edit_question: function() {
                this.$emit('editquestion');
                this.show_edit = false;
            },
            delete_question: function() {
                this.$emit('deletequestion', this.question.id);
            }
        },
        computed: {
            total_responses: function() {
                if(this.question.sessions.length == 0) {
                    return 0;
                }
                return this.question.sessions.reduce(function (accumulator, session) {

                    return accumulator + parseInt(session.responses.length);
                }, 0);
            },
            check: {
                get: function() {
                    if(this.question.current_session_id) {
                        var session = this.question.sessions.find(s => s.id == this.question.current_session_id);
                        console.log(this.question.sessions);
                        return session;
                    }
                    else {
                        return false;
                    }

                },
                set: function(newValue) {
                    if(newValue == true) {
                        const url = (
                            '/api/chime/'
                            + this.folder.chime_id
                            + '/folder/'
                            + this.folder.id
                            + '/question/'
                            + this.question.id);

                        axios.post(url, {})
                        .then(res => {

                        })
                        .catch(err => {
                            console.log(err.response);
                        });
                    }
                    else {
                        const url = (
                            '/api/chime/'
                            + this.folder.chime_id
                            + '/folder/'
                            + this.folder.id
                            + '/question/'
                            + this.question.id
                            + '/stopSession/'
                            );

                        axios.put(url, {})
                        .then(res => {

                        })
                        .catch(err => {
                            console.log(err.response);
                        });
                    }
                }

            }
        }

    }
</script>

<style scoped>
.pointer {
    cursor: pointer;
}
.card-title {
    margin: 0 auto;
    max-width: 500px;
}

.response_label {
    display: inline-block;
    border-radius: 10px;
    background-color: lightblue;
    padding-left: 5px;
    padding-right: 5px;
}

.flow-text {
    display: inline-block;
    width: 90%;
}

.pretty {
    margin-right: 0;
}

.p-switch {
    vertical-align: top;
    margin-top: 6px;
}

</style>


