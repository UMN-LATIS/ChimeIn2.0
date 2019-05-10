<template>
    <modal :show="show" @close="close">
        <div class="modal-header">
            <h3>Add a Question</h3>
        </div>
        <div class="modal-body">
            <div class="row">
                 <div class="col-sm-3">
                    <label for="fodler" class="col-form-label">Folder</label>
                </div>
                <div class="col-sm-9">
                    <div class="form-group" v-if="folders">
                       <v-select v-model="folder_id" :options="folders" label="name" :reduce="folder => folder.id" :clearable="false"></v-select>
                    </div>
                </div>
                <div class="col-sm-3">
                    <label for="questionType" class="col-form-label">Question Type</label>
                </div>
                <div class="col-sm-9">
                    <div class="form-group">
                        <v-select v-model="question_type" :options="question_types"  :reduce="question_type => question_type.id" :clearable="false"></v-select>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col">
                    <div class="form-check">
                        <label class="form-check-label">
                            <input type="checkbox" class="form-check-input" v-model="anonymous">
                            Anonymous Question
                        </label>
                    </div>
                </div>
            </div>
            <hr>
            <div class="row">
                <div class="col">
                    <vue-editor v-model="question_text" placeholder="Question Text" v-bind:editorToolbar="toolbar"
                        v-bind:useCustomImageHandler="true" v-on:imageAdded="handle_image_added">
                    </vue-editor>
                </div>
            </div>

            <component :is="question_type + '_response'" :question_responses.sync="question_responses"></component>
        </div>
        <div class="modal-footer text-right">
            <button class="btn btn-secondary" @click="close">
                Cancel
            </button>
            <button class="btn btn-primary" @click="savePost">
                Save
            </button>

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
    import {
        VueEditor,
        Quill
    } from 'vue2-editor'
    import MultipleChoiceResponse from "./response_components/MultipleChoice.vue";
    import ImageResponse from "./response_components/ImageResponse.vue";
    import SliderResponse from "./response_components/SliderResponse.vue";
    import FreeResponse from "./response_components/FreeResponse.vue";
    import TrueFalseResponse from "./response_components/TrueFalse.vue";

    import VueSelect from 'vue-select';

    const Embed = Quill.import('blots/embed');

    class ImageBlot extends Embed {
        static create(value) {
            let node = super.create();
            node.setAttribute('src', value.url);
            node.setAttribute('class', "responsive-img");
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
            'true_false_response': TrueFalseResponse,
            'v-select':VueSelect
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
                question_types: [
                    {id:"multiple_choice", label:"Multiple Choice"},
                    {id:"true_false", label:"True/False"},
                    {id:"free_response", label:"Free Response"},
                    {id:"image_response", label:"Image Response"},
                    {id:"slider_response", label:"Slider Response"}
                    ],
                toolbar: [
                    ['bold', 'italic', 'underline', 'align'],
                    [{
                        'list': 'ordered'
                    }, {
                        'list': 'bullet'
                    }],
                    ['code-block', 'image']
                ]
            }
        },
        mounted() {
            axios.get('/api/chime/' + this.folder.chime_id)
            .then(res => {
                this.folders = res.data.folders;
            })
            .catch(err => {
                console.log(err);
            });
        },
        methods: {
            close: function () {
                this.$emit('close');
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

                if (this.question.id) {
                    url = url + "/question/" + this.question.id;
                    axios.put(url, {
                            question_text: question.text,
                            question_info: question.question_info,
                            anonymous: this.anonymous,
                            folder_id: this.folder_id
                        })
                        .then(res => {
                            console.log(res);
                            this.$emit('edited');
                        })
                        .catch(err => {
                            console.log(err.response);
                        });
                } else {
                    axios.post(url, {
                            question_text: question.text,
                            question_info: question.question_info,
                            anonymous: this.anonymous?true:false,
                            folder_id: this.folder_id
                        })
                        .then(res => {
                            console.log(res);
                            this.close();
                        })
                        .catch(err => {
                            console.log(err.response);
                        });
                }


            },
            handle_image_added: function (file, editor, cursor, reset) {
                console.log('file:', file);

                let form_data = new FormData();
                form_data.append('image', file);

                axios.post(
                        '/api/chime/' +
                        this.folder.chimeId +
                        '/image', form_data)
                    .then(res => {
                        editor.insertEmbed(cursor, 'image', {
                            url: res.data
                        });
                        reset();
                    })
                    .catch(err => {
                        console.log(err.response)
                    });
            }
        }
    }
</script>