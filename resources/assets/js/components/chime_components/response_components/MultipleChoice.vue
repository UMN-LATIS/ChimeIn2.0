<template>  
    <div>
        <div class="row">
            <div class="col">
                <div class="form-check">
                <input class="form-check-input" type="checkbox" name="truefalse" id="truefalse" v-model="truefalse" @change="toggleTrueFalse">
                <label class="form-check-label" for="truefalse">True/False Question</label>
            </div>
            </div>
        </div>
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
                        v-on:click="edit(i)">edit</i>
                        
                            <i
                        class="material-icons pointer inline-icon"
                        v-on:click="remove(i)">delete</i>
                        </div>
                    </div>
                    </li>
                    </draggable>
            </ol>
        </div>
    </div>
    <div class="row" v-if="!truefalse || editing_index !== null">
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
                class="validate form-control"
                v-bind:disabled="truefalse">
                <div class="input-group-append ">
                    <button @click="add_choice" class="btn btn-outline-primary  align-items-center d-flex"><span class="material-icons ">{{ editing_index !==null ? "save":"add" }}</span></button>
                </div>
            </div>
        </div>
    </div>
</div>
</template>

<style scoped>
.input-group-text {
    background-color: white;
}
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
             editing_index: null,
             truefalse: false
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
        toggleTrueFalse: function()   {
            if(this.truefalse && !this.isTrueFalse()) {
                this.question_responses.push({"text":"True","correct":false});
                this.question_responses.push({"text":"False","correct":false});
            }
        },
        isTrueFalse: function() {
            if(this.question_responses && JSON.stringify(this.question_responses.map(e=>e.text)) == JSON.stringify(["True", "False"])) {
                return true;
            }
            return false;
        }
    },
    mounted() {
        if(!this.question_responses) {
            this.$emit('update:question_responses', []);
        }

        this.truefalse = this.isTrueFalse();
    }
}
</script>
