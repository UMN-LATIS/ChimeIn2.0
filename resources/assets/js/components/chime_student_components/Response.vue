<template>
    <div class="row responseContainer">
        <div
            class="col-12"
            v-if="response.session.question">
            <p class="questionText" v-html="response.session.question.text"></p>
            <component :question="response.session.question"
                :disabled="true"
                :response="response"
                :is="response.session.question.question_info.question_type">
            </component>
            
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
    props: ['response']
};
</script>
