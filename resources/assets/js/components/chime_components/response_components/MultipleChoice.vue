<template>  
    <div>
        <div class="row choiceRow">
            <div class="col">
                <p>Answers:</p>
                <ol type="A">
                    <draggable :list="question_responses">
                    <li v-for="(r, i) in question_responses"
                    :key="i"
                    >
                    <div class="row">

                        <div class="col-10 dragItem">
                            {{ isObject(r)?r.text:r }}
                        </div>
                        <div class="col-1">
                            <i
                        class="material-icons pointer deleteIcon"
                        v-on:click="() => remove(i)">delete</i>
                        </div>
                    </div>
                </li>
                    </draggable>
            </ol>
        </div>
    </div>
    <div class="row">
        <div class="col">
            <label for="choice_text" class="form-control-label">Add a choice <small id="emailHelp" class="form-text text-muted">Optionally use the checkbox to mark correct answers.</small></label>
            
            <div class="input-group">
                 <div class="input-group-prepend">
                    <div class="input-group-text">
                        <input type="checkbox" v-model="choice_correct" aria-label="Correct Answer">
                    </div>
                </div>
                <input
                id="choice_text"
                v-model="choice_text"
                type="text"
                @keyup.enter="add_choice"
                class="validate form-control">
                <div class="input-group-append ">
                    <button @click="add_choice" class="btn btn-outline-primary  align-items-center d-flex"><span class="material-icons ">add</span></button>
                </div>
            </div>
        </div>
    </div>
</div>
</template>

<style scoped>
.deleteIcon {
    vertical-align: middle !important;
}
.choiceRow {
    margin-top: 5px;
    margin-bottom: 5px;
}

.dragItem {
    cursor: move;
}
</style>

<script>
// TODO this is mutating a prop from the parent, it shouldn't do that.
// 
    export default {
        props: ['question_responses'],
        components: {

        },
        data: function() {
            return {
             choice_text: "",
             choice_correct: false
         }
     },
     methods: {
       
        remove: function(response_index) {
            this.$delete(this.question_responses, response_index);
        },
        add_choice: function() {
            this.question_responses.push({"text": this.choice_text, "correct": this.choice_correct});
            this.choice_text = '';
            this.choice_correct = false;
        },   
    },
    mounted() {
        if(!this.question_responses) {
            this.$emit('update:question_responses', []);
        }
    }
}
</script>
