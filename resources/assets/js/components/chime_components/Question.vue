<template>
    <li>
    <b-row>
        <b-col sm=9>
            <question-form :show="show_edit" @edited="edit_question" @close="show_edit = false;"
            :question="question"
            :folder="folder"
            :chime="chime"
            controlType="edit">
        </question-form>
        <div>
            <p class="response_label">{{ total_responses }}</p>
            <p class="flow-text" v-html="question.text"></p>
    </div>
</b-col>
<b-col sm=3>
    <div class="float-right">

        <a 
        class="pointer"
        v-bind:href="'/chime/' + folder.chime_id + '/folder/' + folder.id + '/present/#/' + (question.order - 1)">
        <i class="material-icons">play_arrow</i>
    </a>
        <a 
        class="pointer"
        v-on:click="show_edit = !show_edit">
        <i class="material-icons">edit</i>
    </a>
    <a 
    class="pointer"
    v-on:click="delete_question">
    <i class="material-icons ">delete</i>
</a>
</div>
</b-col>

</b-row>
</li>
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
        edit_question: function() {
            this.$emit('editquestion');
            this.show_edit = false;
        },
        delete_question: function() {
            this.$emit('deletequestion', this.question.id);
        }
    },
    computed: {
        total_responses: function() {
            if(this.question.sessions.length == 0) {
                return 0;
            }
            return this.question.sessions.reduce(function (accumulator, session) {

                return accumulator + parseInt(session.responses.length);
            }, 0);
        }
    }
}
</script>

<style scoped>
.pointer {
    cursor: pointer;
}
.card-title {
    margin: 0 auto;
    max-width: 500px;
}

.response_label {
    display: inline-block;
    border-radius: 10px;
    background-color: lightblue;
    padding-left: 5px;
    padding-right: 5px;
}

.flow-text {
    display: inline-block;
}
</style>


