<template>
    <b-row>
        <b-col>
            <b-row>
                <template v-if="show_edit_folder">
                    <b-col sm="8">
                        <input
                        id="edit-folder-input"
                        v-model="new_folder_name"
                        type="text"
                        @keyup.esc="toggle_edit_folder"
                        @keyup.enter="edit_folder">
                    </b-col>
                    <b-col sm="1">
                        <a
                        class="btn-small waves-effect waves-light"
                        v-on:click="toggle_edit_folder">
                        <i class="material-icons">clear</i></a>
                    </b-col>
                    <b-col sm="1">
                        <a
                        class="btn-small waves-effect waves-light"
                        v-on:click="edit_folder">
                        <i class="material-icons">save</i></a>
                    </b-col>
                </template>
                <template v-else v-on:click="toggle_edit_folder">

                    <b-col sm="9">
                        <a href="#" v-on:click="show_questions = !show_questions" v-if="questions.length > 0">
                            <i class="material-icons float-left" v-if="show_questions">expand_less</i>
                            <i class="material-icons float-left" v-if="!show_questions">expand_more</i>
                        </a>
                        <h4 @click="show_edit_folder = true">{{ folder.name }}</h4>
                    </b-col>
                    <b-col sm="3" class="text-right">
                        <a href="#" v-on:click="delete_folder">
                            <i class="material-icons">delete</i>
                        </a>

                        <a class="" v-bind:href="'/chime/' + folder.chime_id + '/folder/' + folder.id + '/present'">
                            <i class="material-icons">play_arrow</i>
                        </a>

                        <a href="#" v-on:click="">
                            <i class="material-icons" @click="showModal = true">add</i>
                        </a>

                    </b-col>
                </template>


            <question-form :show="showModal" @close="showModal = false; load_questions();"
            :question="{
            text:'',
            question_info: {
            question_type:'multiple_choice',
            question_responses: []
        }
    }"
    :folder="folder"
    :chime="chime"
    controlType="create">
</question-form>
</b-row> 
<vue-slide-up-down :active="show_questions" :duration="500">
    <draggable v-model="questions" @end=swap_question>
            <question
            v-for="q in questions"
            :key="q.id"
            :folder="folder"
            :chime="chime"
            :question="q"
            v-on:editquestion="update_question"
            v-on:deletequestion="delete_question">
        </question>
    </draggable>
</vue-slide-up-down> 
  
    </b-col>
</b-row>
</template>

<script>
import draggable from 'vuedraggable'

export default {
    props: ['folder', 'chime'],
    data() {
        return {
            showModal: false,
            content: 'present',
            questions: [],
            show_edit_folder: false,
            show_questions: false,
            new_folder_name: this.folder.name
        }
    },
    methods: {
        toggle_edit_folder: function() {
            this.show_edit_folder = (this.show_edit_folder ? false : true);
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
        },
        load_questions() {
            const url = (
                '/api/chime/' + this.folder.chime_id + '/folder/' + this.folder.id);


            axios.get(url)
            .then(res => {
                this.questions = res.data;
                console.log('questions:', this.questions);
            })
            .catch(err => {
                console.log(err);
            });

        }
    },
    created: function() {
        this.load_questions();
        
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
