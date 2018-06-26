<template>
    <div>
        <div v-if="response.response_info">
            <img class="responsive-img imageContainer" v-bind:src="'/storage/' + response.response_info.image">
        </div>

        
        <b-form-file v-if="!disabled" accept="image/*" v-model="file" :state="Boolean(file)" placeholder="Choose an image..."></b-form-file>
    </div>
</template>

<style>

.imageContainer {
    max-width: 400px;
    max-height: 400px;
}

</style>

<script>
export default {
    props: ['question', 'response', 'disabled', 'chime'],
    data() {
        return {
            "file": ""
        }
    },
    watch: {
        file: function(value){

            let form_data = new FormData();
            form_data.append('image', value);


            axios.post(
                '/api/chime/'
                + this.chime.id
                + '/image', form_data)
            .then(res => {
                 const response = {
                    question_type: 'image_response',
                    image: res.data.image,
                    image_name: value.name
                }

                this.$emit('recordresponse', response);
            });

        },
    },
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

                const response = {
                    question_type: 'image_response',
                    image: res.data.image,
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
