<template>  
    <div>
         <div class="form-check">
              <input
                class="form-check-input"
                type="checkbox"
                id="hideWordcloud"
                v-model="hideWordcloud" @change="emitChanges"
              >
              <label class="form-check-label" for="hideWordcloud">Hide wordcloud</label>
            </div>
            

    </div>
</template>



<script>
    
export default {
	props: ["question_responses"],
	data: function() {
		return {
            hideWordcloud: this.question_responses.hideWordcloud
		}
    },
    
	methods: {
		emitChanges: function() {
			this.$emit('update:question_responses', {"hideWordcloud": this.hideWordcloud});
		}
    }
}
</script>
