<template>
    <div class="card hoverable">
        <div class="card-content">
            <div class="container" v-if="show_edit">
                <div class="input-field col s12">
                    <question-form
                        :question="question"
                        :chime="chime"
                        v-on:submitquestion="edit_question"
                    ></question-form>
                </div>
            </div>
            <div v-else>
                <div class="card">
                    <div class="card-content">
                        <p class="flow-text" v-html="question.text"></p>
                    </div>
                </div>

                <multiple-choice-display
                    v-if="question.question_info.question_type === 'multiple_choice'"
                    :question="question">
                </multiple-choice-display>
            </div>
        </div>
        <div class="card-action">
            <a
                class="pointer"
                v-bind:href="
                    '/chime/' + folder.chime_id
                    + '/folder/' + question.folder_id
                    + '/present'
                    + '?question_id=' + question.id">
                Present
            </a>
            <a 
                class="pointer"
                v-on:click="show_edit ? show_edit = false : show_edit = true">
                {{ show_edit ? 'Back' : 'Edit'}}
            </a>
            <a 
                class="pointer"
                v-on:click="delete_question">
                Delete
            </a>
        </div>
    </div>
</template>

<script>
export default {
    props: ['folder', 'question', 'chime'],
    data: function() {
        return {
            show_edit: false
        }
    },
    methods: {
        edit_question: function(edited_question) {
            this.$emit('editquestion', edited_question);
            this.show_edit = false;
        },
        delete_question: function() {
            this.$emit('deletequestion', this.question.id);
        }
    }
}
</script>

<style>
    .card-title {
        margin: 0 auto;
        max-width: 500px;
    }
</style>


