<template>
<div>
    <div v-if="responses.length > 0" class="row">
        <div class="col">
            <download-csv class="btn btn-info" :data="csv_data">Export CSV</download-csv>
            <lightbox v-bind:id="'lightbox' + question.id " :images="images" image_class="img-responsive img-rounded" :options="options">
            </lightbox>
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
    props: ['responses', 'question'],
    data: function () {
        return {
            visible_responses: [],
            response_search: '',
            options: {
                closeText: 'X'
            }
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
                    "user": r.user_id,
                    "session": r.session_id,
                    "image": r.response_info.image
                }
            });
            return rows;
        }
    }
}
</script>
