<template>
    <div>
        <navbar
        title="Back to Chime"
        :user="user"
        :link="{name:'chime', params:{chimeId: chimeId}}">
    </navbar>
    <div class="container">
        <b-row>
            <b-col sm="9">
                <h4 v-if="!show_edit_folder">{{ folder.name }}</h4>
                
                <b-input-group v-if="show_edit_folder">
                    <b-form-input v-model="folder.name"></b-form-input>
                    <b-input-group-append>
                        <b-btn variant="primary" class="align-items-center d-flex" @click="edit_folder"><span class="material-icons pointer">save</span> Save</b-btn>
                    </b-input-group-append>
                    
                </b-input-group>
            </b-col>
            <b-col sm="3" class="text-right">

                <router-link :to="{ name: 'present', params: {chimeId: chimeId, folderId: folderId} }">
                    <i class="material-icons">play_arrow</i>
                </router-link>
                <i class="material-icons pointer" v-on:click="show_edit_folder = !show_edit_folder">edit</i>
                <i class="material-icons pointer" v-on:click="delete_folder">delete</i>
                <i class="material-icons pointer" @click="showModal = true">add</i>

            </b-col>
        </b-row>
        <b-row>
            <b-col sm="12">
         <ul>
            <draggable v-model="questions" @end=swap_question>
                <question
                v-for="q in questions"
                :key="q.id"
                :folder="folder"
                :question="q"
                v-on:editquestion="load_questions"
                v-on:deletequestion="delete_question">
            </question>
        </draggable>
    </ul>
</b-col>
</b-row>
</div>
    <question-form :show="showModal" @close="showModal = false; load_questions();"
                :question="{
                text:'',
                question_info: {
                question_type:'multiple_choice',
                question_responses: []
            }
        }"
        :folder="folder"
        :chimeId="chimeId"
        controlType="create">
    </question-form>

</div>
</template>

<script>
    import draggable from 'vuedraggable'
    import { questionsListener } from './mixins/questionsListener'


    export default {
        props: ['folderId', 'chimeId', 'user'],
        mixins: [questionsListener],
        data() {
            return {
                folder: {name: ""},
                showModal: false,
                content: 'present',
                questions: [],
                show_edit_folder: false,
                show_questions: false,
                new_folder_name: "",
            }
        },
        methods: {
            swap_question(event, originalEvent) {

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

                this.load_questions();

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
                    this.$nextTick(function () {
                        this.$refs.slideup.layout();    
                    });
                })
                .catch(err => {
                    console.log(err.response);
                });
            },
            edit_folder: function() {
            const self = this;
                axios.put('/api/chime/' + this.chimeId + '/folder/' + this.folderId, {
                    folder_name: this.folder.name
                })
                .then(res => {
                    this.show_edit_folder = false;
                })
                .catch(err => {
                    console.error(err);
                });
            },
            delete_folder: function() {
            const confirm = window.confirm(
                'Delete Folder ' + this.folder.name + '?');
            
            if (confirm) {
                const url = (
                    '/api/chime/' + this.chimeId + /folder/ + this.folderId
                    );

                axios.delete(url)
                .then(res => {
                    this.$router.push({ name: 'chime', params: { chimeId: this.chimeId}})
                })
                .catch(err => {
                    console.log(err.response);
                });
            }
            },
            load_folder: function() {

            }
        },
        created: function() {
            this.load_folder();
            this.load_questions();

        },

        components: {
            draggable
        }
    };
</script>

<style >
.pointer {
    cursor: pointer;
}
</style>
<style scoped>
ul li {
    list-style: none;
}

</style>
