<template>
  <div>
    <h4>{{project.name}}</h4>
    <highstock :options="options" ref="hddram"></highstock>
  </div>
</template>

<script>
  export default {
    data() {
      return {
        project: this.$parent.project,

        options: {
          chart: {
            type: 'line',
            zoomType: 'x'
          },
          plotOptions: {
            series: {
                showInNavigator: true
            }
          },
          scrollbar: {
            liveRedraw: false
          },
          rangeSelector: {
            buttons: [{
                type: 'hour',
                count: 1,
                text: '1h'
            }, {
                type: 'day',
                count: 1,
                text: '1d'
            }, {
                type: 'month',
                count: 1,
                text: '1m'
            }, {
                type: 'year',
                count: 1,
                text: '1y'
            }, {
                type: 'all',
                text: 'All'
            }],
            inputEnabled: false, // it supports only days
            selected: 1 // default is 1 day
          },
          xAxis: {
            events: {
              afterSetExtremes: this.afterSetExtremes
            },
            minRange: 3600 * 1000
          },
          yAxis: {
            floor: 0
          },
          series: [
            {
              name: 'hdd',
              data: []
            },
            {
              name: 'ram',
              data: []
            }
          ],
          credits: false
        }
      }
    },
    methods: {
      afterSetExtremes(e) {
        let names = ['hdd', 'ram'];
        for (let [i, name] of names.entries()) {
          axios.get('/api/project/' + this.project.slug + '/usage/' + name)
          .then(({data}) => this.$refs.hddram.chart.series[i].setData(data))
          .catch(error => console.log(error))
        }
      }
    },
    mounted() {
      this.afterSetExtremes({min:0, max:1})
    }
  }
</script>
