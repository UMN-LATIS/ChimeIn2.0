<template>
    <div>
        <div v-if="responses.length > 0" class="row">
            <div class="col">
            <a
                class="waves-effect waves-light btn-small"
                id="csv_link"
                v-on:click="export_csv">
                Export CSV
            </a>

           <lightbox
                v-bind:id="'lightbox' + question.id "
                :images="images"
                :image_class=" 'img-responsive img-rounded' "
                :options="options">
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

export default {
    components: {
      Lightbox
    },
    props: ['responses', 'question'],
    data: function() {
        return {
            visible_responses: [],
            response_search: '',
            options : {
                closeText : 'X'
            }
        }
    },
    computed: {
        images: function() {
            return this.responses.map(elem => { return {"src": '/storage/' + elem.response_info.image, "title":""} });
        }
    },
    methods: {
        export_csv: function() {
            console.log(this.responses);

            const rows = this.responses.map(r => {
                return [
                    r.user.id,
                    r.user.name,
                    r.session_id,
                    r.response_info.image,
                    r.response_info.image_name].join(',')
            });

            let row_str = 'User Id,User Name,Session Id,Image URL,Image Name\n'
            row_str += rows.join('\n');
            
            // console.log(row_str);

            const link = document.getElementById('csv_link');
            const file = new Blob([row_str], {type: 'text/csv'});

            link.href = URL.createObjectURL(file);
            link.download = 'question_' + this.question.id + '_responses.csv';
        }
    },
}
</script>
