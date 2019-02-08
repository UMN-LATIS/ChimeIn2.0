<template>
    <div>
        <div class="row">
            <div class="form-group col">
                <input type="range" class="form-control-range custom-range" id="formControlRange">
            </div>
        </div>
        <div class="row justify-content-between">
            <div class="col-sm-2">
                {{ question.question_info.question_responses.left_choice_text}}
            </div>
            <div class="col-sm-2 text-right">
                {{question.question_info.question_responses.right_choice_text}}
            </div>
        </div>
    </div>
</template>

<script>
export default {
    props: ['question']
};
</script>


<style>



</style>