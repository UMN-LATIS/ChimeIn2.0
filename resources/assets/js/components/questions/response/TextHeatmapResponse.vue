<template>
    <div>
        <div class="col-sm-12" >
            <div
                data-cy="text-heatmap-highlighted-text-container"
                class="form-group"
                v-html="highlightedText"
            />
            <div class="col-sm-12">
                <button v-if="(!disabled && !response.id) || create_new_response" class="btn btn-primary" variant="primary" @click="record_response" :disabled="disableSubmission">Submit Selection</button>
                <button v-if="!disabled && response.id && !create_new_response && response.response_info.startOffset < 0" class="btn btn-primary" variant="primary" @click="record_response" :disabled="disableSubmission">Update</button>
                <button v-if="!disabled && response.id && !create_new_response && response.response_info.startOffset >= 0" class="btn btn-primary" variant="primary" @click="resetSelection">Reset Selection</button>
                <button v-if="!disabled && response.id && !create_new_response && question.allow_multiple" class="btn btn-primary" variant="primary" @click="new_response">Clear and Start a New Response</button>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    props: ['question', 'response', 'disabled'],
    data() {
        return {
            create_new_response: false,
            disableSubmission: true
        }
    },
    watch: {
        response: function(value) {
            if(this.response && this.response.response_info) {
                // this.response_text = this.response.response_info.text;    
            }
            
        }
    },
    computed: {
        highlightedText: function() {
            var insideAnElement = false;
            var outputString = "";
            if(this.startOffset < 0 || this.create_new_response) {
                return this.question.question_info.question_responses.heatmap_text;
            }
            for (var i = 0; i < this.question.question_info.question_responses.heatmap_text.length; i++) {
                let currentCharacter = this.question.question_info.question_responses.heatmap_text[i];
                // we need to not inject new html inside tags, but we need to track our position
                if(currentCharacter == "<") {
                    insideAnElement = true;
                }   
                if(insideAnElement) {
                    outputString = outputString + currentCharacter;
                }
                    
                if(currentCharacter == ">") {
                    insideAnElement = false;
                    continue;
                }

                if(insideAnElement) {
                    continue;
                }

                // super easy way to make a heatmap gradient based on https://stackoverflow.com/questions/12875486/what-is-the-algorithm-to-create-colors-for-a-heatmap
                
                var backgroundColor = null;
                if(i < this.startOffset) {
                    backgroundColor = "";
                }
                else if(i >= this.startOffset && i < this.endOffset) {
                    backgroundColor = "background-color: yellow";
                }
                
                outputString =
                    outputString +
                    "<span style='" +
                    backgroundColor +
                    "'>" +
                    currentCharacter +
                    '</span>';
            }
            return outputString;
        },
        startOffset: function() {
            if(this.response && this.response.response_info) {
                return this.response.response_info.startOffset;
            }
            return -1;
        },
        endOffset: function() {
            if(this.response && this.response.response_info) {
                return this.response.response_info.endOffset;
            }
            return -1;
        }
    },
    methods: {
        record_response: function() {            
            const mySelection = window.getSelection();
            // console.log(mySelection);
            var startOffset = 0;
            var endOffset = 0;
            // if the selection is not empty (aka does not have same start and end point), grab the offsets
            if (!mySelection.isCollapsed) {
                var range = window.getSelection().getRangeAt(0);

                // plain text of selected range (if you want it w/o html)
                var text = window.getSelection();
                // document fragment with html for selection
                var fragment = range.cloneContents();
                // make new element, insert document fragment, then get innerHTML!
                var div = document.createElement('div');
                div.appendChild( fragment.cloneNode(true) );

                // your document fragment to a string (w/ html)! (yay!)
                var html = div.innerHTML;
                var div2 = document.createElement('div');
                div2.innerHTML = this.question.question_info.question_responses.heatmap_text
                startOffset = div2.innerHTML.indexOf(html);
                endOffset = startOffset + html.length;
                const response = {
                    question_type: 'text_heatmap_response',
                    startOffset: startOffset,
                    endOffset: endOffset
                }

                this.$emit('recordresponse', response, this.create_new_response);
                this.create_new_response = false;
            }
            else {
                //if there isn't a selection, reset what's stored
                this.resetSelection();
            }
            
            
        },
        resetSelection() {
            window.getSelection().removeAllRanges();
            const response = {
                question_type: 'text_heatmap_response',
                startOffset: -1,
                endOffset: -1
            }

            this.$emit('recordresponse', response, this.create_new_response);
            return;
        },
        new_response: function() {
            window.getSelection().removeAllRanges();
            this.create_new_response = true;
        },
        testForHighlight: function() {
            const mySelection = window.getSelection();
            if(mySelection.isCollapsed) {
                this.disableSubmission = true;
            }
            else {
                this.disableSubmission = false;
            }
        }
    },
    mounted() {
        if(this.response && this.response.hasOwnProperty('response_info') && this.response.response_info.hasOwnProperty('startOffset')) {
            // this.response_text = this.response.response_info.text;
        }
    },
    created: function() {
        window.addEventListener('mouseup',this.testForHighlight);
    },
    destroyed: function() {
        window.removeEventListener('mouseup', this.testForHighlight);
    }
};
</script>
