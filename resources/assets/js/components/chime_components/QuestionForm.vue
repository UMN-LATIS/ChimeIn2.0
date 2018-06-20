<template>  
     <modal :show="show" @close="close">
        <div class="modal-header">
            <h3>Add Question</h3>
        </div>
      <div class="modal-body">
    
            <div class="input-field col s12">
                <vue-editor
                    v-model="question_text"
                    placeholder="Question Text"
                    v-bind:editorToolbar="toolbar"
                    v-bind:useCustomImageHandler="true"
                    v-on:imageAdded="handle_image_added">
                </vue-editor>
            </div>
        </div>
        <div class="row">
            <div class="input-field col s12">
                <select id="question_type" v-model="question_type">
                    <option value="multiple_choice">Multiple Choice</option>
                    <option value="free_response">Free Response</option>
                    <option value="image_response">Image Response</option>
                </select>
                <label for="question_type">Question Type</label>
            </div>
        </div>
        <ol type="A" v-if="question_type === 'multiple_choice'">
            <li v-for="(r, i) in question_responses"
                :key="i"
                class="left-align">
                {{ r }}
                <span>
                    <i
                        class="material-icons right pointer"
                        v-on:click="() => remove(i)">delete</i>
                </span>
            </li>
        </ol>
        <div class="row" v-if="question_type === 'multiple_choice'">
            <div class="input-field col s12">
                <input
                    id="choice_text"
                    v-model="choice_text"
                    type="text"
                    @keyup.enter="add_choice"
                    class="validate">
                <label for="choice_text">Choice Text</label>
            </div>
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

<script>
import { VueEditor, Quill } from 'vue2-editor'

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
    props: ['question', 'chime', 'show', 'folder', 'controlType'],
    data: function() {
        return {
            choice_text: '',
            question_text: this.question.text,
            question_type: this.question.question_info.question_type,
            question_responses: this.question.question_info.question_responses,
            toolbar: [
                ['bold', 'italic', 'underline', 'align'],
                [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                ['code-block', 'image']]
        }
    },
    methods: {
        close: function () {
            this.$emit('close');
            if(this.controlType == "create") {
                this.choice_text = "";
                this.question_text = "";
                this.question_type = "multiple_choice";
                this.question_responses = [];    
            }
            
        },
        savePost: function () {
             const url = (
                '/api/chime/' + this.folder.chime_id +
                '/folder/' + this.folder.id);
            console.log('question:', question)

            var question = {};
            question.text = this.question_text;
            question.question_info = {
                question_type: this.question_type,
                question_responses: this.question_responses
            };

            axios.post(url, {
                question_text: question.text,
                question_info: question.question_info,
            })
            .then(res => {
                console.log(res);
                this.close();
            })
            .catch(err => {
                console.log(err.response);
            });
      
        },
        remove: function(response_index) {
            this.$delete(this.question_responses, response_index);
        },
        add_choice: function() {
            this.question_responses.push(this.choice_text);
            this.choice_text = '';
        },
        handle_image_added: function(file, editor, cursor, reset) {
            console.log('file:', file);

            let form_data = new FormData();
            form_data.append('image', file);

            axios.post(
                '/api/chime/'
                + this.chime.id
                + '/image', form_data)
            .then(res => {
                editor.insertEmbed(cursor, 'image', {url: res.data});
                reset();
            })
            .catch(err => {
                console.log(err.response)
            });
        }
    }
}
</script>
