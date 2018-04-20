<template>
    <div class="card">
        <div class="card-content grey lighten-4">
            <a href="#" v-on:click="delete_folder">
                <i class="material-icons right">delete</i>
            </a>
            <h4>{{ folder.name }}</h4>
        </div>
        <div class="card-tabs">
            <ul class="tabs tabs-fixed-width">
                <li class="tab">
                    <a
                        v-on:click="content = 'present'"
                        class="hoverable pointer"
                        v-bind:class="{ 'active': content === 'present' }">
                    Present</a>
                </li>
                <li class="tab">
                    <a
                        v-on:click="content = 'questions'"
                        class="hoverable pointer"
                        v-bind:class="{ 'active': content === 'questions' }">
                    Questions</a>
                </li>
                <li class="tab">
                    <a
                        v-on:click="content = 'new_question'"
                        class="hoverable pointer"
                        v-bind:class="{ 'active': content === 'present' }">
                    New Question</a>
                </li>
            </ul>
        </div>
        <div class="card-content">
            <div v-if="content === 'present'">
                <a
                    class="waves-effect waves-light btn-large"
                    v-bind:href="
                        '/chime/' + folder.chime_id
                        + '/folder/' + folder.id
                        + '/present'">
                    <i class="material-icons left">play_arrow</i>
                    Start Presentation
                </a>
            </div>
            <div v-else-if="content === 'questions'">
                <question
                    v-for="q in questions"
                    :key="q.id"
                    :folder="folder"
                    :chime="chime"
                    :question="q"
                    v-on:editquestion="update_question"
                    v-on:movedown="swap_question"
                    v-on:deletequestion="delete_question">
                </question>
            </div>
            <div  class="container" v-else>
                <question-form
                    :question="{
                        text:'',
                        question_info: {
                            question_type:'multiple_choice',
                            question_responses: []
                        }
                    }"
                    v-on:submitquestion="create_question"
                    :folder="folder"
                    :chime="chime">
                </question-form>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    props: ['folder', 'chime'],
    data() {
        return {
            content: 'present',
            questions: []
        }
    },
    methods: {
        create_question(question) {
            const url = (
                '/api/chime/' + this.folder.chime_id +
                '/folder/' + this.folder.id);
            const self = this;

            console.log('question:', question)

            axios.post(url, {
                question_text: question.text,
                question_info: question.question_info,
            })
            .then(res => {
                console.log(res);
                res.data.question_info = JSON.parse(res.data.question_info)
                self.questions.push(res.data);
                self.content = 'questions';
            })
            .catch(err => {
                console.log(err.response);
            });
        },
        update_question(question) {
            const url = (
                '/api/chime/' + this.folder.chime_id +
                '/folder/' + this.folder.id + '/question/' + question.id);
            const self = this;
            console.log(question)

            axios.put(url, {
                question_text: question.text,
                question_info: question.question_info,
            })
            .then(res => {
                console.log(res);
            })
            .catch(err => {
                console.log(err);
            });
        },
        swap_question(question) {
            const question_index = this.questions.findIndex(
                e => e.id === question.id);
            const next_index = (question_index + 1) % this.questions.length;
            const temp = this.questions[next_index];

            const url = (
                '/api/chime/' + this.folder.chime_id +
                '/folder/' + this.folder.id + '/question/' + question.id
                + '/move_down');
            const self = this;

            axios.put(url, {})
            .then(res => {
                console.log(res);
                self.questions[next_index] = self.questions[question_index];
                self.questions[question_index] = temp;
                self.questions = self.questions.map(e => e);
            })
            .catch(err => {
                console.log(err);
            });
        },
        delete_question(questionId) {
            const url = (
                '/api/chime/' + this.folder.chime_id +
                '/folder/' + this.folder.id + '/question/' + questionId);
            const self = this;

            axios.delete(url)
            .then(res => {
                console.log(res);
                const question_index = self.questions.findIndex(
                    e => e.id === questionId);
                self.questions.splice(question_index, 1);
            })
            .catch(err => {
                console.log(err.response);
            });
        },
        delete_folder() {
            this.$emit('deletefolder', this.folder);
        }
    },
    created: function() {
        const url = (
            '/api/chime/' + this.folder.chime_id + '/folder/' + this.folder.id);
        const self = this;

        axios.get(url)
        .then(res => {
            self.questions = res.data.questions;
            self.questions.forEach(e => {
                e.question_info = JSON.parse(e.question_info);
            });
            console.log('questions:', self.questions);
        })
        .catch(err => {
            console.log(err);
        });
    }
}
</script>

<style>
    .pointer {
        cursor: pointer;
    }
    
    li {
        font-size: 1.5em;
    }
</style>
