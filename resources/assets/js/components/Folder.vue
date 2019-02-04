<template>
    <div>
        <navbar
        title="Back to Chime"
        :user="user"
        :link="{name:'chime', params:{chimeId: chimeId}}">
    </navbar>
    <div class="alert alert-warning" role="alert" v-if="!hideOpenAlert && otherFolderSessions.length > 0">
        You have {{ otherFolderSessions.length }} questions open outside this folder.  Would you like to <a class="pointer" href="" @click.prevent="closeOthers">close them</a>?<a class="float-right pointer" @click="hideOpenAlert = true">X</a>
    </div>
    <div class="container">
        <div class="row mt-2">
            <div class="col-4 align-items-center d-flex">
                <h4 v-if="!show_edit_folder">{{ folder.name }}</h4>
                <div class="input-group mb-3" v-if="show_edit_folder">
                  <input type="text" class="form-control" v-model="folder.name">
                  <div class="input-group-append">
                    <button class="btn btn-primary align-items-center d-flex" @click="edit_folder"><span class="material-icons pointer">save</span> Save</button>
                    </div>
                </div>
            </div>
            <div class="col-md-8 col-sm-12">
                <div class="btn-group float-right" style="flex-wrap: wrap;" role="group" aria-label="Folder Controls">
                    <router-link :to="{ name: 'present', params: {chimeId: chimeId, folderId: folderId} }"  tag="button" class="btn btn-sm btn-outline-info align-items-center d-flex">
                        Present
                        <i class="material-icons">play_arrow</i>
                    </router-link>
                    <button class="btn btn-sm  btn-outline-info align-items-center d-flex" @click="show_edit_folder = !show_edit_folder">Edit <i class="material-icons pointer">edit</i></button>
                    <button class="btn btn-sm btn-outline-info align-items-center d-flex" @click="delete_folder">Delete <i class="material-icons pointer">delete</i></button>
                    <button class="btn btn-sm btn-outline-info align-items-center d-flex" @click="showModal = true">New Question <i class="material-icons pointer">add</i></button>
                    <button class="btn btn-sm btn-outline-info align-items-center d-flex" @click="openAll">Open All <i class="material-icons pointer">visibility</i></button>
                    <button class="btn btn-sm btn-outline-info align-items-center d-flex" @click="closeAll">Close All <i class="material-icons pointer">visibility_off</i></button>
              
        </div>
    </div>
    </div>

    <div class="row border-top mt-3 pt-3">
        <div class="col-sm-12">
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
</div>
</div>
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
    
    const QuestionForm = () => import(
        /* webpackChunkName: "QuestionForm" */
        './chime_components/QuestionForm.vue'
        );

    export default {
        props: ['folderId', 'chimeId', 'user'],
        mixins: [questionsListener],
        components: {
            draggable, 'question-form': QuestionForm
        },
        data() {
            return {
                folder: {name: ""},
                showModal: false,
                content: 'present',
                questions: [],
                show_edit_folder: false,
                show_questions: false,
                new_folder_name: "",
                allSessions: null,
                hideOpenAlert: false,
            }
        },
        computed: {
            otherFolderSessions: function() {
                if(this.allSessions && this.folder.id) {
                    return this.allSessions.filter(e => e.question.folder_id != this.folder.id);
                }
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
                axios.get('/api/chime/' + this.chimeId + '/openQuestions')
                    .then(res => {
                        this.allSessions = res.data.sessions;
                })
            },
            
            openAll: function() {
                for(var question of this.folder.questions) {
                    const url = (
                            '/api/chime/'
                            + this.folder.chime_id
                            + '/folder/'
                            + this.folder.id
                            + '/question/'
                            + question.id
                            );

                        axios.post(url, {})
                        .then(res => {

                        })
                        .catch(err => {

                        });
                }
            },
            closeAll: function() {
                for(var question of this.folder.questions) {
                    const url = (
                            '/api/chime/'
                            + this.folder.chime_id
                            + '/folder/'
                            + this.folder.id
                            + '/question/'
                            + question.id
                            + '/stopSession/'
                            );

                        axios.put(url, {})
                        .then(res => {

                        })
                        .catch(err => {

                        });
                }
            },
            closeOthers: function() {
                for(var openSession of this.otherFolderSessions) {
                    const url = (
                            '/api/chime/'
                            + this.folder.chime_id
                            + '/folder/'
                            + openSession.question.folder_id
                            + '/question/'
                            + openSession.question.id
                            + '/stopSession/'
                            );

                        axios.put(url, {})
                        .then(res => {

                        })
                        .catch(err => {

                        });
                }
                this.hideOpenAlert = true;
            }
        },
        created: function() {
            this.load_folder();
            this.load_questions();
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

.align-items-center h4 {
    margin-bottom: 0;
}

.btn-group .btn i.material-icons {
    margin-left: 2px;
}

</style>
