<template>
    <div>
        <a
        href="#"
        class="btn btn-outline-primary"
        id="csv_link"
        v-on:click.stop="export_csv">
        Export CSV
    </a>
    <reactive-bar-chart :chart-data="chartData" :options="options" :styles="myStyles"></reactive-bar-chart>
</div>
</template>

<script>


    import ReactiveBarChart from "../../ReactiveBarChart.js";

    export default {
        props: ['responses', 'question'],
        components: {
            ReactiveBarChart
        },
        data: function() {
            return {
                visible_responses: [],
                response_search: '',
                options: { 
                    scales: {
                        yAxes: [{
                            ticks: {
                                beginAtZero: true,
                                userCallback: function(label, index, labels) {
                                    if (Math.floor(label) === label) {return label;}
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
            myStyles () {
              return {
                // height: `${this.height}px`,
                position: 'relative'
            }
        },
        chartData: function() {
            return {labels: this.question.question_info.question_responses,
                datasets: [
                {
                    label: 'Number of Responses',
                    backgroundColor: '#0b3c4c',
                    data:  this.question.question_info.question_responses.map(
                        q => this.responses.filter(r => q === r.response_info.choice).length
                        )
                }
                ]
            };
        }
    },
    methods: {
        export_csv: function() {
            const rows = this.responses.map(r => {
                return [
                r.user.id,
                r.user.name,
                r.session_id,
                r.response_info.choice].join(',')
            });

            let row_str = 'User Id,User Name,Session Id,Choice\n'
            row_str += rows.join('\n');
            
            console.log(row_str);

            const link = document.getElementById('csv_link');
            const file = new Blob([row_str], {type: 'text/csv'});

            link.href = URL.createObjectURL(file);
            link.download = 'question_' + this.question.id + '_responses.csv';
        }
    }
}
</script>
