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
                    xAxes: [{
                            display: false,
                            barPercentage: 1.3,
                            ticks: {
                                max: 0.2,
                            }
                        },

                        {
                            display: true,
                            ticks: {
                                autoSkip: false,
                                max: 0.2,
                            }
                        }
                    ],
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
        binnedValues() {
            return consHistObjWithBins(this.responses.map(r => r.response_info), {
                min: 0,
                max: 106,
                width: 6
            })('choice');
        },
        labels() {
            return [this.question.question_info.question_responses.left_choice_text, "", "", "", "", "", "", "", "", "", "", "", "", "", this.question.question_info.question_responses.right_choice_text];
        },
        values() {
            return Object.values(this.binnedValues);
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
            return {
                labels: this.labels,
                datasets: [{
                    label: 'Number of Responses',
                    backgroundColor: '#0b3c4c',
                    data: this.values
                }]
            };
        }
    }
}

/*
 * Sum.
 * sum :: (Number, Number) → Number
 * 
 * Provided two numbers, return their summation.
 **/
const sum = (numX, numY) => numX + numY

/*
 * Increment.
 * incr :: Number → Number
 * 
 * Provided a number, return its value incremented by one.
 **/
const incr = num => sum(num, 1)

/*
 * Construct bins.
 * consBins :: Object → [[Number]]
 * 
 * Configuration parameters:
 * min - The numerical floor from which to initialize the first interval.
 * max - The numerical ceiling by which we limit the last of intervals.
 * width - The range encompassed by each interval.
 **/
const consBins = ({
        min = 0,
        max = Infinity,
        width = 1,
        accum = []
    }) =>
    // Ensure `max` does not exceed the range of our current interval...
    sum(min, width) > max ? // If so, return our accumulator;
    accum : // Otherwise, call upon our method recursively until we fulfill our
    // predicating condition.
    consBins({
        // Increment interval, so as to preclude overlap.
        min: incr(sum(min, width)),
        // `max` and `width` remain constant.
        max,
        width,
        // Wax upon our accumulator, combining it with a new array, in which the
        // current interval is represented.
        accum: accum.concat([
            [min, sum(min, width)]
        ]),
    })

/*
 * Filter property by minimum and maximum values.
 * filterPropByMinMax :: Object → Array
 * 
 * From a collection, return only those cases where the given property falls
 * within range of specified minimum and maximum values.
 *
 * Configuration parameters:
 * {String} key - The property to -- and by which to -- filter.
 * {String} min - The minimum value by which to filter our attribute.
 * {String} min - The maximum value by which to filter our attribute.
 **/
const filterPropByMinMax = (arr, {
        key = '',
        min = '0',
        max = '100'
    }) =>
    arr.filter(
        // The numerical values encompassed by our JSON list assume the form of
        // strings; hence, we need abstract their numerical values via `parseInt`.
        // Our callback will return `true` provided the item's value is greater than
        // or equal to our minimum _and_ less than or equal to our maximum.
        item => parseInt(item[key], 10) >= min && parseInt(item[key], 10) <= max
    )

/*
 * Construct histogram object.
 * consHistObj :: (Array, [[Number]]) → String → Object
 **/
const consHistObj = (arr = [], bins = []) => (key = '') =>
    // For each of the nested arrays within out `bins` list...
    bins.reduce(
        (accum, currVal) => ({
            // Build upon our accumulator, which we initialize as an empty object...
            ...accum,
            // And where each entry is an object, whereof the the key shall represent
            // the minimum and maximum values encompassed by each bin (e.g., `15,19`),
            // and whereof the value shall delineate all vehicles encompassed by such
            // bin. We retrieve such values by of `filterPropByMinMax`...
            [currVal]: filterPropByMinMax(arr, {
                key,
                min: currVal[0],
                max: currVal[1],
                // And we calculate a total by way of `Array.prototype.length`.
            }).length,
        }), {}
    )

/*
 * Construct histogram object provided bins.
 * consHistObjWithBins :: (Object → [Object] → String) → Object
 * 
 * Configuration parameters:
 * {Number} min - The minimum value by which to filter our attribute.
 * {Number} min - The maximum value by which to filter our attribute.
 * {Number} width - The range encompassed by each interval.
 **/
const consHistObjWithBins = (arr = [], {
    min = 0,
    max = 100,
    width = 1
}) => (
    key = ''
) => consHistObj(arr, consBins({
    min,
    max,
    width
}))(key)
</script>
