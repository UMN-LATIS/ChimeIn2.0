<template>
    <div>
        <div v-if="responses.length > 0">
            <a
                class="waves-effect waves-light btn-small"
                id="csv_link"
                v-on:click="export_csv">
                Export CSV
            </a>

            <carousel :autoplay="true" :perPage="2" :autoplayHoverPause="true">
                <slide v-for="r in responses" :key="r.id">
                    <img class="responsive-img" :src="r.response_info.image">
                </slide>
            </carousel>
        </div>

        <div v-else>No Responses Yet!</div>

        <input
            id="response_search_input"
            type="text"
            v-model="response_search"
            v-on:keyup="filter_responses">
        <label for="response_search_input">Student Name</label>

        <ul>
            <li v-for="r in visible_responses" :key="r.id">
                {{r.user.name}}: <img class="responsive-img" :src="r.response_info.image">
            </li>
        </ul>
    </div>
</template>

<script>
import { Carousel, Slide } from 'vue-carousel';

export default {
    props: ['responses', 'question'],
    data: function() {
        return {
            visible_responses: [],
            response_search: '',
            word_groups: []
        }
    },
    methods: {
        shuffle: function(a) {
            for (let i = a.length - 1; i > 0; i--) {
                const j = Math.floor(Math.random() * (i + 1));
                [a[i], a[j]] = [a[j], a[i]];
            }

            return a;
        },
        filter_responses: function() {
            if (this.response_search) {
                this.visible_responses = this.responses.filter(r => {
                    return r.user.name.indexOf(this.response_search) > -1
                });
            } else {
                this.visible_responses = [];
            }
        },
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
    created: function() {
        $(document).ready(function(){
            $('.carousel').carousel();
        });
    },
    watch: {
        responses: function() {
            this.shuffle(this.responses);
        }
    },
    components: {
        Carousel,
        Slide
    }
}
</script>
