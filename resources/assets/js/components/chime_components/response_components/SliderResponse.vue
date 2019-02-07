<template>  
    <div>
        <div class="row form-group">
                <label for="left_choice_text" class="col-form-label col-sm-2">Left Label: </label>
                <div class="input-group col-sm-10">
                    <input
                    id="left_choice_text"
                    v-model="left_choice_text"
                    @input="emitChanges"
                    type="text"
                    class="validate form-control">
                    
                </div>
        </div>
        <div class="row form-group">
                <label for="right_choice_text" class="col-form-label col-sm-2">Right Label:</label>
                <div class="input-group col-sm-10">
                    <input
                    id="right_choice_text"
                    v-model="right_choice_text"
                    @input="emitChanges"
                    type="text"
                    class="validate form-control">
                    
                </div>
        </div>
    </div>
</template>


<script>
    
export default {
	props: ["question_responses"],
	data: function() {
		return {
			left_choice_text: this.question_responses.left_choice_text,
			right_choice_text: this.question_responses.right_choice_text
		}
	},
	methods: {
		emitChanges: function() {
			this.$emit('update:question_responses', {"left_choice_text": this.left_choice_text, "right_choice_text":this.right_choice_text});
		}
	}
}
</script>
