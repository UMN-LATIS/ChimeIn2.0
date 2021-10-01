<template>
    <div>
        <navbar title="Back to Chime" :user="user" :link="{name:'chime', params:{chimeId: chimeId}}">
        </navbar>
        <error-dialog />
        <div class="alert alert-warning" role="alert" v-if="!hideOpenAlert && otherFolderSessions.length > 0">
            You have {{ otherFolderSessions.length }} {{ 'question' | pluralize(otherFolderSessions.length) }} open
            outside this folder. Would you like to <a class="pointer" href="" @click.prevent="closeOthers">close
                {{ otherFolderSessions.length == 1 ? 'it': 'them' }}</a>?<a class="float-right pointer"
                @click="hideOpenAlert = true">X</a>
        </div>
        <div class="container">
            <div class="row mt-2">
                <div class="col-4 align-items-center d-flex">
                    <h1 class="h4">{{ folder.name }}</h1>

                </div>
                <div class="col-md-8 col-sm-12">
                    <div class="btn-group float-right" style="flex-wrap: wrap;" role="group"
                        aria-label="Folder Controls">
                        <button class="btn btn-sm  btn-outline-info align-items-center d-flex"
                            @click="show_edit_folder = !show_edit_folder">Folder Settings <i
                                class="material-icons pointer">edit</i></button>
                        <router-link :to="{ name: 'present', params: {chimeId: chimeId, folderId: folderId} }"
                            tag="button" class="btn btn-sm btn-outline-info align-items-center d-flex">
                            Present
                            <i class="material-icons">play_arrow</i>
                        </router-link>
                        <button dusk="new-question-button" data-cy="new-question-button" class="btn btn-sm btn-outline-info align-items-center d-flex"
                            @click="showModal = true">New Question <i class="material-icons pointer">add</i></button>
                        <button dusk="open-all-button" class="btn btn-sm btn-outline-info align-items-center d-flex"
                            @click="openAll">Open All <i class="material-icons pointer">visibility</i></button>
                        <button dusk="close-all-button" class="btn btn-sm btn-outline-info align-items-center d-flex"
                            @click="closeAll">Close All <i class="material-icons pointer">visibility_off</i></button>
                        <router-link :to="{ name: 'chimeStudent', params: {chimeId: chimeId} }" tag="button"
                            class="btn btn-sm btn-outline-info align-items-center d-flex">
                            Preview
                            <i class="material-icons">search</i>
                        </router-link>

                    </div>
                </div>
            </div>
            <div v-if="show_edit_folder" class="ml-4 mt-2">
                <div class="row ">
                    <div class="col-12">
                        <div class="input-group mb-3">
                            <input type="text" class="form-control" v-model="folder.name">

                            <div class="input-group-append">
                                <button class="btn btn-outline-primary align-items-center d-flex"
                                    @click="edit_folder"><span class="material-icons pointer">save</span>
                                    Save</button>

                            </div>
                        </div>
                    </div>
                    <div class="ml-auto col-12 btn-toolbar justify-content-end">
                        <button class="mr-2 btn btn-success btn-sm align-items-center d-flex" @click="sync" v-if="folder.resource_link_pk > 0">
                            Force Sync with Canvas <span class="material-icons md-18" v-if="synced">check_circle</span>
                        </button>
                        <button class="mr-2 btn btn-warning btn-sm align-items-center d-flex" @click="reset">
                            Reset Folder
                        </button>
                        <button class="btn btn-sm btn-danger align-items-center d-flex" @click="delete_folder">Delete Folder <i class="material-icons pointer md-18">delete</i>
                        </button>
                    </div>
                </div>

                <hr>
                <fieldset class="form-group border p-2">
                    <legend class="col-form-label w-auto">Import Questions</legend>
                    <div class="row">
                        <div class="col-sm-12">
                            <div class="form-group">
                                <label for="chime_select">Select a Chime:</label>
                                <select id="chime_select" class="form-control" v-model="selected_chime"
                                    @change="update_folders">
                                    <option disabled>Select a Chime</option>
                                    <option v-for="chime in existing_chimes" :key="chime.id" :value="chime.id">
                                        {{ chime.name }}</option>
                                </select>
                            </div>


                            <div class="form-group">
                                <label for="folder_select">Select a Folder:</label>
                                <select id="folder_select" class="form-control" v-model="selected_folder">
                                    <option disabled>Select a Folder</option>
                                    <option v-for="folder in existing_folders" :key="folder.id" :value="folder.id">
                                        {{ folder.name }}</option>
                                </select>
                            </div>
                            <button class="btn btn-primary" @click="do_import">Import</button>
                        </div>
                    </div>
                </fieldset>


            </div>

            <div class="row border-top mt-3 pt-3">
                <div class="col-sm-12">
                    <ul>
                        <draggable v-model="questions" data-cy="question-list" @end="swap_question" handle=".draghandle">
                            <question v-for="q in questions" :key="q.id" :folder="folder" :question="q"
                                v-on:editquestion="load_questions" v-on:deletequestion="delete_question">
                            </question>
                        </draggable>
                    </ul>
                </div>
            </div>
        </div>
        <question-form v-if="showModal" :show="showModal" @close="showModal = false; load_questions();" :question="{
    text:'',
    question_info: {
        question_type:'multiple_choice',
        question_responses: null
    }
}" :folder="folder" :chimeId="chimeId" controlType="create">
        </question-form>

    </div>
</template>

<script>
    import draggable from 'vuedraggable'
    import {
        questionsListener
    } from './mixins/questionsListener'
    import JsonCSV from 'vue-json-csv'



    const QuestionForm = () => import(
        /* webpackChunkName: "QuestionForm" */
        './chime_components/QuestionForm.vue'
    );

    export default {
        props: ['folderId', 'chimeId', 'user'],
        mixins: [questionsListener],
        components: {
            draggable,
            'question-form': QuestionForm,
            'downloadCsv': JsonCSV
        },
        data() {
            return {
                folder: {
                    name: ""
                },
                showModal: false,
                content: 'present',
                questions: [],
                show_edit_folder: false,
                show_questions: false,
                new_folder_name: "",
                allSessions: null,
                hideOpenAlert: false,
                existing_chimes: [],
                existing_folders: [],
                selected_chime: null,
                selected_folder: null,
                synced: false
            }
        },
        computed: {
            otherFolderSessions: function () {
                if (this.allSessions && this.folder.id) {
                    return this.allSessions.filter(e => e.question.folder_id != this.folder.id);
                }
                return [];
            }
        },
        watch: {
            show_edit_folder: function (newValue) {
                if (newValue) {
                    this.loadExistingChimes();
                }
            }
        },
        methods: {
            reset: function () {
                if (confirm("Are you sure you want to wipe all the responses to questions in this folder?")) {
                    var promises = [];
                    for (var question of this.questions) {
                        const url = ('/api/chime/' + this.folder.chime_id + '/folder/' + this.folder.id +
                            '/question/' + question.id + "/responses");
                        promises.push(axios.delete(url));
                    }
                    Promise.all(promises).then(() => {
                        this.load_questions();
                    });

                }
            },
            swap_question(event, originalEvent) {

                const newOrder = Array.from(this.questions.entries()).map(e => {
                    return {
                        order: e[0] + 1,
                        id: e[1].id
                    }
                });

                console.log(newOrder);
                const url = (
                    '/api/chime/' +
                    this.folder.chime_id +
                    '/folder/' +
                    this.folder.id +
                    '/save_order'
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
                if (confirm("Are you sure you want to remove this question?")) {
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
                }
            },
            edit_folder: function () {
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
            delete_folder: function () {
                const confirm = window.confirm(
                    'Delete Folder ' + this.folder.name + '?');

                if (confirm) {
                    const url = (
                        '/api/chime/' + this.chimeId + /folder/ + this.folderId
                    );

                    axios.delete(url)
                        .then(res => {
                            this.$router.push({
                                name: 'chime',
                                params: {
                                    chimeId: this.chimeId
                                }
                            })
                        })
                        .catch(err => {
                            console.log(err.response);
                        });
                }
            },
            load_folder: function () {
                axios.get('/api/chime/' + this.chimeId + '/openQuestions')
                    .then(res => {
                        this.allSessions = res.data.sessions;
                    })
            },

            openAll: function () {
                const url = (
                    '/api/chime/' +
                    this.folder.chime_id +
                    '/folder/' +
                    this.folder.id +
                    '/question/startAll'
                );

                axios.post(url, {})
                    .then(res => {

                    })
                    .catch(err => {

                    });
            },
            closeAll: function () {
                const url = (
                    '/api/chime/' +
                    this.folder.chime_id +
                    '/folder/' +
                    this.folder.id +
                    '/question/stopAll'
                );

                axios.put(url, {})
                    .then(res => {

                    })
                    .catch(err => {

                    });
            },
            closeOthers: function () {
                for (var openSession of this.otherFolderSessions) {
                    const url = (
                        '/api/chime/' +
                        this.folder.chime_id +
                        '/folder/' +
                        openSession.question.folder_id +
                        '/question/' +
                        openSession.question.id +
                        '/stopSession/'
                    );

                    axios.put(url, {})
                        .then(res => {

                        })
                        .catch(err => {

                        });
                }
                this.hideOpenAlert = true;
            },
            loadExistingChimes: function () {

                axios.get('/api/chime')
                    .then(res => {
                        this.existing_chimes = _.orderBy(res.data, 'created_at', ['desc'])
                    })
                    .catch(err => {
                        console.error(
                            'error', 'Error in get chimes:', err.response);
                    });
            },
            update_folders: function () {
                axios.get('/api/chime/' + this.selected_chime)
                    .then(res => {
                        this.existing_folders = _.orderBy(res.data.folders.filter(f => f.id != this.folderId),
                            'created_at', ['desc'])
                    })
                    .catch(err => {
                        console.error(
                            'error', 'Error in get chimes:', err.response);
                    });
            },
            do_import: function () {
                if (!this.selected_chime || !this.selected_folder) {
                    return;
                }
                axios.post("/api/chime/" + this.chimeId + "/folder/" + this.folderId + "/import", {
                        "folder_id": this.selected_folder
                    })
                    .then(res => {
                        this.load_folder();
                        this.load_questions();
                    })
                    .catch(err => {
                        console.error(
                            'error', 'Error with import:', err.response);
                    });
            },
            sync: function () {
                axios.post("/api/chime/" + this.chimeId + "/folder/" + this.folderId + "/sync")
                    .then(res => {
                        if (res.data.success) {
                            this.synced = true;
                        }
                    })
                    .catch(err => {
                        this.$store.commit('message',
                            "Could not sync Chime. Please contact support at latis@umn.edu.");
                        console.log(err);
                    });
            }
        },
        created: function () {
            this.load_folder();
            this.load_questions();
        }
    };
</script>

<style>
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