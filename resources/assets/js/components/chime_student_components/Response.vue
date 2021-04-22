<template>
    <div class="row responseContainer">
        <div
            class="col-12"
            v-if="response.session && response.session.question">
            <p class="questionText" v-html="response.session.question.text"></p>
            <component :question="response.session.question"
                :disabled="true"
                :response="response"
                :is="response.session.question.question_info.question_type">
            </component>
            <a class="pointer" v-if="chime.students_can_view" v-bind:href="'/chime/' + chime.id + '/folder/' + response.session.question.folder_id + '/present/' + (response.session.question.order - 1)">View Responses</a>
           
            <small class="text-muted" v-if="chime.show_folder_title_to_participants"><strong>Folder</strong>: {{ response.session.question.folder.name }} </small>
            <hr />

        </div>
    </div>
</template>


<style>
.responseContainer {
    /*border: 1px solid black;
    border-radius: 5px;
    margin: 5px;*/
}

</style>

<script>
export default {
    props: ['response', 'chime']
};
</script>
