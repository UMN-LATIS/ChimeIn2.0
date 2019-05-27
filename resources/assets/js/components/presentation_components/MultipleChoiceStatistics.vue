<template>
<div>
    <download-csv class="btn btn-info" :data="csv_data">Export CSV</download-csv>
    <reactive-bar-chart :chart-data="chartData" :options="options" :styles="myStyles"></reactive-bar-chart>
</div>
</template>

<script>
import JsonCSV from 'vue-json-csv'

const ReactiveBarChart = () => import(
    /* webpackChunkName: "ReactiveBarChart" */
    '../../ReactiveBarChart.js'
);
// import ReactiveBarChart from "../../ReactiveBarChart.js";

export default {
    props: ['responses', 'question'],
    components: {
        ReactiveBarChart,
        "downloadCsv": JsonCSV
    },
    data: function () {
        return {
            visible_responses: [],
            response_search: '',
            options: {
                scales: {
                    yAxes: [{
                        ticks: {
                            beginAtZero: true,
                            userCallback: function (label, index, labels) {
                                if (Math.floor(label) === label) {
                                    return label;
                                }
                            }
                        }
                    }]
                },
                responsive: true,
                maintainAspectRatio: false
            }
        }
    },
    computed: {
        myStyles() {
            return {
                // height: `${this.height}px`,
                position: 'relative'
            }
        },
        csv_data: function () {
            const rows = this.responses.map(r => {
                return {
                    "user": r.user_id,
                    "session": r.session_id,
                    "response": r.response_info.choice
                }
            });
            return rows;
        },
        chartData: function () {
            return {
                labels: this.question.question_info.question_responses,
                datasets: [{
                    label: 'Number of Responses',
                    backgroundColor: '#0b3c4c',
                    data: this.question.question_info.question_responses.map(
                        q => this.responses.filter(r => q === r.response_info.choice).length
                    )
                }]
            };
        }
    }
}
</script>
