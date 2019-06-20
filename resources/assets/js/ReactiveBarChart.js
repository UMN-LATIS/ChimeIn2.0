import { Bar, mixins } from "vue-chartjs";
const { reactiveProp } = mixins;
export default {
  extends: Bar,
  mixins: [reactiveProp],
  props: ["options"],
  mounted() {
    // this.chartData is created in the mixin.
    // If you want to pass options please create a local options object
    this.addPlugin({
      id: 'label-fix',
      beforeInit: function (chart) {
        chart.data.labels.forEach(function (value, index, array) {
          var a = [];
          a.push(value.slice(0, 5));
          var i = 1;
          while (value.length > (i * 5)) {
            a.push(value.slice(i * 5, (i + 1) * 5));
            i++;
          }
          array[index] = a;
        })
      }
      });
    this.renderChart(this.chartData, this.options);
  }
};