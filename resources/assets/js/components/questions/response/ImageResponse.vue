<template>
    <div>
        <div v-if="response.response_info">
            <img class="responsive-img" :src="response.response_info.image">
        </div>

        <div class="file-field input-field">
            <div class="btn">
                <span>Image</span>
                <input
                    type="file"
                    v-bind:disabled="disabled"
                    v-bind:id="'question_' + question.id + '_response'"
                    @change="record_response($event)"/>
            </div>
            <div class="file-path-wrapper">
                <input
                    class="file-path"
                    v-bind:disabled="disabled"
                    type="text"
                    v-bind:value="
                        response.response_info ? response.response_info.image_name : ''"/>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    props: ['question', 'response', 'disabled'],
    methods: {
        record_response: function(event) {
            const self = this;
            const file = event.target.files[0];
            console.log(file);
            
            let form_data = new FormData();
            form_data.append('image', file);

            axios.post(
                '/api/chime/'
                + window.location.pathname.split('/')[2]
                + '/image', form_data)
            .then(res => {
                console.log(res);
                // self.image = res.data;

                /*
                const response_text = document.getElementById(
                    'question_' + this.question.id + '_response').value;
            
                const response = {
                    question_type: 'free_response',
                    image: response_text
                }
                */

                const response = {
                    question_type: 'image_response',
                    image: res.data,
                    image_name: file.name
                }

                this.$emit('recordresponse', response);
            })
            .catch(err => {
                console.log(err.response)
            });
        },
    }
}
</script>
