<template>
<div class="chartContainer">
    <download-csv class="btn btn-info" :data="csv_data">Export CSV</download-csv>
    <GChart
    type="ColumnChart"
    :resizeDebounce="100"
    :data="chartData"
    :options="options"
    class="googleChart"
  />
</div>
</template>

<style>

.chartContainer {
    /* min-height: 20vh; */
    height: 600px;
}

.googleChart {
    height: 100%;
}

</style>

<script>
import JsonCSV from 'vue-json-csv'

import { GChart } from 'vue-google-charts'


export default {
    props: ['responses', 'question'],
    components: {
        GChart,
        "downloadCsv": JsonCSV
    },
    data: function () {
        return {
            visible_responses: [],
            response_search: '',
            options: {
                height: "100%",
                animation:{
                    duration: 1000,
                    easing: 'out',
                    "startup":  true
                },
                legend: {
                    position: "none"
                },
                chartArea: {
                    top:30,
                    left: 50,
                    width: "100%"
                },
                vAxis: {
                    baseline: 0,
                    format: "#",
                }
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
                    "user": r.user.name,
                    "email": r.user.email,
                    "session": r.session_id,
                    "response": r.response_info.choice
                }
            });
            return rows;
        },
        chartData: function () {
            var questionArray = this.question.question_info.question_responses.map(q => { return [q, this.responses.filter(r => Array.isArray(r.response_info.choice)?r.response_info.choice.includes(q):(r.response_info.choice == q)).length, 'color: rgb(54, 162, 235); opacity: 0.4; stroke-opacity: 0.9; stroke-width: 2'] })
            questionArray.unshift(['Answer', 'Number of Responses', { role: 'style' }])
            return questionArray;
        }
    }
}
</script>
