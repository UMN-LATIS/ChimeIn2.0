<template>
    <div>
        <line-chart :word-groups="word_groups"></line-chart>

        <input
            id="response_search_input"
            type="text"
            v-model="response_search"
            v-on:keyup="filter_responses">
        <label for="response_search_input">Student Name</label>

        <ul>
            <li v-for="r in visible_responses" :key="r.id">
                {{r.user.name}}: {{r.response_info.text}}
            </li>
        </ul>
    </div>
</template>

<script>
const VueChartJs = require('vue-chartjs');
const difflib = require('difflib');
const cluster = require('set-clustering');

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
        filter_responses: function() {
            if (this.response_search) {
                this.visible_responses = this.responses.filter(r => {
                    return r.user.name.indexOf(this.response_search) > -1
                });
            } else {
                this.visible_responses = [];
            }
        },
        similarity: function(x, y) {
            return (new difflib.SequenceMatcher(null, x, y)).ratio();
        }
    },
    components: {
        'line-chart': {
            props: ['wordGroups'],
            extends: VueChartJs.Bar,
            methods: {
                render_chart: function() {
                    console.log('word groups:', this.wordGroups);

                    this.renderChart({
                    labels: this.wordGroups.map(g => g[0]),
                    datasets: [
                        {
                        label: 'Number of Responses',
                        backgroundColor: '#0b3c4c',
                        data: this.wordGroups.map(g => g.length)
                        }
                    ]
                    }, {responsive: true, maintainAspectRatio: false})
                }
            },
            watch: {
                wordGroups: function() {
                    this.render_chart();
                }
            },
            mounted: function() {
                this.render_chart();
            }
        }
    },
    watch: {
        responses: function() {
            if (this.responses.length > 0) {
                this.word_groups = cluster(
                    this.responses.map(r => r.response_info.text),
                    this.similarity).groups(0.9);
            } else {
                this.word_groups = [];
            }
        }
    },
    created: function() {
        if (this.responses.length > 0) {
            this.word_groups = cluster(
                this.responses.map(r => r.response_info.text),
                this.similarity).groups(0.9);
        }
    }
}
</script>
