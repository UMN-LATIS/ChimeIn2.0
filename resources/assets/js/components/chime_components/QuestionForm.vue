<template>
    <modal :show="show" @close="close">
        <div class="modal-header">
            <h3>Add a Question</h3>
        </div>
        <div class="modal-body">
            <div class="row">
                <div class="col-sm-3">
                    <label for="folder" class="col-form-label">Folder</label>
                </div>
                <div class="col-sm-9">
                    <div class="form-group" v-if="folders">
                        <v-select v-model="folder_id" :options="folders" label="name" :reduce="folder => folder.id"
                            :clearable="false"></v-select>
                    </div>
                </div>
                <div class="col-sm-3">
                    <label for="questionType" class="col-form-label">Question Type</label>
                </div>
                <div class="col-sm-9">
                    <div class="form-group">
                        <v-select v-model="question_type" :options="question_types"
                            :reduce="question_type => question_type.id" :clearable="false"></v-select>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-6">
                    <div class="form-check">
                        <label class="form-check-label">
                            <input type="checkbox" class="form-check-input" v-model="anonymous">
                            Anonymous Question
                        </label>
                    </div>
                </div>
                <div class="col-6">
                    <div class="form-check">
                        <label class="form-check-label">
                            <input type="checkbox" class="form-check-input" v-model="allow_multiple">
                            Allow Multiple Responses
                        </label>
                    </div>
                </div>
            </div>
            <hr>
            <div class="row">
                <div class="col">
                    <vue-editor v-model="question_text" placeholder="Question Text" v-bind:editorToolbar="toolbar"
                        v-bind:editorOptions="editorOptions" v-bind:useCustomImageHandler="true"
                        v-on:imageAdded="handle_image_added">
                    </vue-editor>
                </div>
            </div>

            <component :is="question_type + '_response'" :question_responses.sync="question_responses"
                :chime_id="this.folder.chime_id"></component>
        </div>
        <div class="modal-footer ">
            <div class="mr-auto">
                <button class="btn btn-danger" @click="reset">
                    Reset Question
                </button>
            </div>
            <div class="">
                <button class="btn btn-secondary" @click="close">
                    Cancel
                </button>
                <button class="btn btn-primary" @click="savePost">
                    Save
                </button>
            </div>

        </div>
    </modal>
</template>

<style scoped>
    .deleteIcon {
        vertical-align: middle !important;
    }

    .choiceRow {
        margin-top: 5px;
        margin-bottom: 5px;
    }
</style>

<script>

    import katex from 'katex';
    window.katex = katex;

    import {
        VueEditor,
        Quill
    } from 'vue2-editor'
    import MultipleChoiceResponse from "./response_components/MultipleChoice.vue";
    import ImageResponse from "./response_components/ImageResponse.vue";
    import SliderResponse from "./response_components/SliderResponse.vue";
    import FreeResponse from "./response_components/FreeResponse.vue";
    import TextHeatmapResponse from "./response_components/TextHeatmapResponse.vue";
    import HeatmapResponse from "./response_components/HeatmapResponse.vue";
    import NoResponse from "./response_components/FreeResponse.vue";

    import VueSelect from 'vue-select';

    const Embed = Quill.import('blots/embed');

    class ImageBlot extends Embed {
        static create(value) {
            let node = super.create();
            node.setAttribute('src', value.url);
            node.setAttribute('class', "img-fluid");
            return node;
        }

        static value(node) {
            return {
                url: node.getAttribute('url')
            };
        }
    }

    ImageBlot.blotName = 'image';
    ImageBlot.tagName = 'img';

    Quill.register(ImageBlot);

    export default {
        props: ['question', 'show', 'folder', 'controlType'],
        components: {
            VueEditor,
            "multiple_choice_response": MultipleChoiceResponse,
            'image_response_response': ImageResponse,
            'slider_response_response': SliderResponse,
            'free_response_response': FreeResponse,
            'text_heatmap_response_response': TextHeatmapResponse,
            'heatmap_response_response': HeatmapResponse,
            'no_response_response': NoResponse,
            'v-select': VueSelect
        },
        data: function () {
            return {
                folders: null,
                choice_text: '',
                question_text: this.question.text,
                question_type: this.question.question_info.question_type,
                question_responses: this.question.question_info.question_responses,
                folder_id: this.folder.id,
                anonymous: this.question.anonymous,
                allow_multiple: this.question.allow_multiple,
                question_types: [{
                        id: "multiple_choice",
                        label: "Multiple Choice"
                    },
                    {
                        id: "free_response",
                        label: "Free Response"
                    },
                    {
                        id: "text_heatmap_response",
                        label: "Text Heatmap"
                    },
                    {
                        id: "image_response",
                        label: "Image Response"
                    },
                    {
                        id: "slider_response",
                        label: "Slider"
                    },
                    {
                        id: "heatmap_response",
                        label: "Heatmap"
                    },
                    {
                        id: "no_response",
                        label: "No Response (placeholder)"
                    }
                ],
                toolbar: [
                    ['bold', 'italic', 'underline', 'align'],
                    [{
                        'list': 'ordered'
                    }, {
                        'list': 'bullet'
                    }],
                    [{
                        "script": "sub"
                    }, {
                        "script": "super"
                    },
                    'formula'],
                    ['link', 'image']
                ],
                editorOptions: {
                    bounds: ".modal-body",
                    modules: {
                        formula: true,
                        keyboard: {
                        bindings: {
                            'list autofill': {
                                prefix: /^\s{0,}(1){1,1}(\.|-|\*|\[ ?\]|\[x\])$/
                            }
                        }
                    }
                    },
                    
                }
            }
        },
        mounted() {
            axios.get('/api/chime/' + this.folder.chime_id)
                .then(res => {
                    this.folders = res.data.folders;
                })
                .catch(err => {
                    this.$store.commit('message', "Could not load this form. The full error was: " + JSON.stringify(err.response).slice(0, 100));
                });
        },
        methods: {
            close: function () {
                this.$emit('close');
            },
            reset: function () {
                if (confirm("Are you sure you want to reset this question, clearing all sessions and responses?")) {
                    const url = ('/api/chime/' + this.folder.chime_id + '/folder/' + this.folder.id + '/question/' +
                        this.question.id + "/responses");
                    const self = this;
                    axios.delete(url)
                        .then(res => {
                            this.$emit('edited');
                        });
                }
            },
            savePost: function () {
                var url = (
                    '/api/chime/' + this.folder.chime_id +
                    '/folder/' + this.folder.id);


                var question = {};
                question.text = this.question_text;


                question.question_info = {
                    question_type: this.question_type,
                    question_responses: this.question_responses
                };
                var responseBlock = {
                    question_text: question.text,
                    question_info: question.question_info,
                    anonymous: this.anonymous,
                    folder_id: this.folder_id,
                    allow_multiple: this.allow_multiple
                };

                if (this.question.id) {
                    url = url + "/question/" + this.question.id;
                    axios.put(url, responseBlock)
                        .then(res => {
                            console.log(res);
                            this.$emit('edited');
                        })
                        .catch(err => {
                            this.$store.commit('message', "Could not store this question.  The full error was: " + JSON.stringify(err.response).slice(0, 100));
                        });
                } else {
                    axios.post(url, responseBlock)
                        .then(res => {
                            console.log(res);
                            this.close();
                        })
                        .catch(err => {
                            this.$store.commit('message', "Could not store this question.  The full error was: " + JSON.stringify(err.response).slice(0, 100));
                        });
                }


            },
            handle_image_added: function (file, editor, cursor, reset) {
                console.log('file:', file);

                let form_data = new FormData();
                form_data.append('image', file);

                axios.post(
                        '/api/chime/' +
                        this.folder.chime_id +
                        '/image', form_data)
                    .then(res => {
                        editor.insertEmbed(cursor, 'image', {
                            url: '/storage/' + res.data.image
                        });
                        reset();
                    })
                    .catch(err => {
                        this.$store.commit('message', "Could not store this image. Please contact support at latis@umn.edu. The full error was: " + err.response);
                    });
            }
        }
    }
</script>