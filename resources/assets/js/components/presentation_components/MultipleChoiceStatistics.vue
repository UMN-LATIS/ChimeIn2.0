<template>
<div class="chartContainer">
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

import { GChart } from 'vue-google-charts';
import isObject from 'lodash/isObject';

export default {
    props: ['responses', 'question'],
    components: {
        GChart,
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
                    format: "%",
                },
                tooltip: {
                    isHtml: false
                },
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
                chartData: function () {
            var questionArray = this.question.question_info.question_responses.map(q => 
            { 
                var questionText = isObject(q) ? q.text : q; 
                var formattedQuestion = questionText;
                if(isObject(q) && q.correct == true) {
                    formattedQuestion = formattedQuestion + " ✓";
                }
                var totalResponsesForAnswer = this.responses.filter(r => Array.isArray(r.response_info.choice)?r.response_info.choice.includes(questionText):(r.response_info.choice == questionText)).length;
                var totalResponsesForQuestion = this.responses.length;
                return [
                    formattedQuestion, 
                    totalResponsesForAnswer / totalResponsesForQuestion,
                    'color: rgb(54, 162, 235); opacity: 0.4; stroke-opacity: 0.9; stroke-width: 2',
                    "Number of Responses:  " + totalResponsesForAnswer + "\n" + "Percentage: " + Math.round((totalResponsesForAnswer / totalResponsesForQuestion) * 10000)/100 + "%"
                    ] 
            });
            questionArray.unshift(['Answer', 'Number of Responses', { role: 'style' }, { type: 'string', role: 'tooltip', 'p': {'html': false} }]);
            return questionArray;
        }
    }
}
</script>
