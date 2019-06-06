<template>
<div>
    <div v-if="responses.length > 0" class="row">
        <div class="col">
            <download-csv class="btn btn-info" :data="csv_data">Export CSV</download-csv>
            <button class="btn btn-warning" data-toggle="button" v-bind:class="{ active: filterImages}" @click="filterImages=!filterImages">Remove Images</button>
            <lightbox v-if="!filterImages" v-bind:id="'lightbox' + question.id " :images="images" image_class="img-responsive img-rounded" :options="options">
            </lightbox>

            
            <table class="table" v-if="filterImages">
                <thead>
                    <tr>
                        <th scope="col">Image</th>
                        <th scope="col">Alt</th>
                        <th scope="col">User</th>
                        <th scope="col">Remove</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="response in responses" v-bind:key="response.id">
                        <td  style="width: 15%"><img :src="'/storage/' + response.response_info.image" class="img-fluid"></td>
                        <td></td>
                        <td>{{ question.anonymous?"Anonymous":response.user.name }} </td>
                        <td><button class="btn btn-danger" @click="removeImage(response)">Delete</button></td>
                    </tr>
                </tbody>
            </table>    
        </div>
    </div>

    <div v-else>No Responses Yet!</div>

</div>
</template>

<style>

</style>

<script>
import Lightbox from 'vue-simple-lightbox'
import JsonCSV from 'vue-json-csv'

export default {
    components: {
        Lightbox,
        "downloadCsv": JsonCSV
    },
    props: ['responses', 'question', "chimeId"],
    data: function () {
        return {
            visible_responses: [],
            response_search: '',
            options: {
                closeText: 'X'
            },
            filterImages: false
        }
    },
    methods: {
        removeImage: function(response) {
            this.$emit("removeResponse", response)
        }
    },
    computed: {
        images: function () {
            return this.responses.map(elem => {
                return {
                    "src": '/storage/' + elem.response_info.image,
                    "title": ""
                }
            });
        },
        csv_data: function () {
            const rows = this.responses.map(r => {
                return {
                    "user": r.user.name,
                    "email": r.user.email,
                    "session": r.session_id,
                    "image": r.response_info.image
                }
            });
            return rows;
        }
    }
}
</script>
