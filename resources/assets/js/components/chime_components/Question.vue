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
</style>


