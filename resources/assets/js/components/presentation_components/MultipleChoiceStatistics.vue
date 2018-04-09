<template>
    <div>
        <line-chart :question="question" :responses="responses"></line-chart>
        
        <input
            id="response_search_input"
            type="text"
            v-model="response_search"
            v-on:keyup="filter_responses">
        <label for="response_search_input">Student Name</label>

        <ul>
            <li v-for="r in visible_responses" :key="r.id">
                {{r.user.name}}: {{r.response_info.choice}}
            </li>
        </ul>
    </div>
</template>

<script>
const VueChartJs = require('vue-chartjs');
export default {
    props: ['responses', 'question'],
    data: function() {
        return {
            visible_responses: [],
            response_search: ''
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
        }
    },
    components: {
        'line-chart': {
            props: ['responses', 'question'],
            extends: VueChartJs.Bar,
            methods: {
                render_chart: function() {
                    this.renderChart({
                    labels: this.question.question_info.question_responses,
                    datasets: [
                        {
                        label: 'Number of Responses',
                        backgroundColor: '#0b3c4c',
                        data:  this
                                .question
                                .question_info
                                .question_responses
                                .map(q => (
                                        this
                                        .responses
                                        .filter(
                                            r => q === r.response_info.choice)
                                        .length
                                    )
                                )
                        }
                    ]
                    }, {responsive: true, maintainAspectRatio: false})
                }
            },
            watch: {
                responses: function() {
                    this.render_chart();
                }
            },
            mounted: function() {
                this.render_chart();
            }
        }
    }
}
</script>
