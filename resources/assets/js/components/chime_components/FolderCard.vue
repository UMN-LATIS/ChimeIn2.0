<template>
    <div class="card">
        <div class="card-content grey lighten-4">
            <a href="#" v-on:click="delete_folder">
                <i class="material-icons right">delete</i>
            </a>
            <div v-if="show_edit_folder">
                <br/>
                <div class="row">
                    <div class="input-field col s10">
                        <input
                            id="edit-folder-input"
                            v-model="new_folder_name"
                            type="text"
                            @keyup.esc="toggle_edit_folder"
                            @keyup.enter="edit_folder">
                    </div>
                    <br/>
                    <div class="input-field col s1">
                        <a
                            class="btn-small waves-effect waves-light"
                            v-on:click="toggle_edit_folder">
                            <i class="material-icons">clear</i>
                        </a>
                    </div>
                    <div class="input-field col s1">
                        <a
                            class="btn-small waves-effect waves-light"
                            v-on:click="edit_folder">
                            <i class="material-icons">save</i>
                        </a>
                    </div>
                </div>
                
            </div>
            <div v-else v-on:click="toggle_edit_folder">
                <h4>{{ folder.name }}</h4>
            </div>
        </div>
        <div class="card-tabs">
            <ul class="tabs tabs-fixed-width">
                <li class="tab">
                    <a
                        v-on:click="content = 'present'"
                        class="pointer"
                        v-bind:class="{ 'active': content === 'present' }">
                    Present</a>
                </li>
                <li class="tab">
                    <a
                        v-on:click="content = 'questions'"
                        class="pointer"
                        v-bind:class="{ 'active': content === 'questions' }">
                    Questions</a>
                </li>
                <li class="tab">
                    <a
                        v-on:click="content = 'new_question'"
                        class="pointer"
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
                <draggable v-model="questions" @end=swap_question>
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
                </draggable>
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
import draggable from 'vuedraggable'

export default {
    props: ['folder', 'chime'],
    data() {
        return {
            content: 'present',
            questions: [],
            show_edit_folder: false,
            new_folder_name: this.folder.name
        }
    },
    methods: {
        toggle_edit_folder: function() {
            this.show_edit_folder = (this.show_edit_folder ? false : true);
        },
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
        swap_question(event, originalEvent) {
            /*
            console.log(element);
            const url = (
                '/api/chime/' + this.folder.chime_id +
                '/folder/' + this.folder.id + '/swap_question');
            const self = this;

            axios.put(url, {
                q1: self.questions[old_i].id,
                q1: self.questions[new_i].id})
            .then(res => {
                console.log(res);
                self.questions[new_i], self.questions[old_i] = self.questions[old_i], self.questions[new_i];
                self.questions = self.questions.map(e => e);
            })
            .catch(err => {
                console.log(err);
            });
            */
            console.log(this.questions);
            const newOrder = Array.from(this.questions.entries()).map(e => {
                return {
                    order: e[0]+1,
                    id: e[1].id
                }
            });

            console.log(newOrder);
            const url = (
                '/api/chime/'
                + this.folder.chime_id
                + '/folder/'
                + this.folder.id
                + '/save_order'
            )

            axios.put(url, {
                question_order: newOrder
            })
            .then(res => {
                console.log(res);
            })
            .catch(err => {
                console.log(err.response);
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
        edit_folder() {
            this.$emit('editfolder', this.folder.id, this.new_folder_name);
            this.show_edit_folder = false;
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

        $(document).ready(function(){
            $('.tabs').tabs();
        });
    },
    components: {
        draggable
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
