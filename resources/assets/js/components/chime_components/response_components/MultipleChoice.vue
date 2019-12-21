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
                       
                        <div class="col-9 dragItem">
                            {{ isObject(r)?r.text:r }}
                        </div>
                         <div class="col-1">
                            <i
                        class="material-icons inline-icon" v-if="isObject(r)?r.correct:false">check</i>
                        </div>
                        <div class="col-2">
                        <i
                        class="material-icons pointer inline-icon"
                        v-on:click="() => edit(i)">edit</i>
                        
                            <i
                        class="material-icons pointer inline-icon"
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
.inline-icon {
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
             choice_correct: false,
             editing_index: null
         }
     },
     methods: {
        edit: function(response_index) {
            var response = this.question_responses[response_index];

            this.choice_text = this.isObject(response)?response.text:response;
            this.choice_correct = this.isObject(response)?response.correct:false;
            this.editing_index = response_index;
        },
        remove: function(response_index) {
            this.$delete(this.question_responses, response_index);
        },
        add_choice: function() {
            if(this.editing_index !== null) {
                this.$set(this.question_responses, this.editing_index, {"text": this.choice_text, "correct": this.choice_correct});
            }
            else {
                this.question_responses.push({"text": this.choice_text, "correct": this.choice_correct});
            }
            
            this.choice_text = '';
            this.choice_correct = false;
            this.editing_index = null;
        },   
    },
    mounted() {
        if(!this.question_responses) {
            this.$emit('update:question_responses', []);
        }
    }
}
</script>
