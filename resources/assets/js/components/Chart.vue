<template>
  <v-card>
    <v-card-title>
      {{ data.title }}
    </v-card-title>
    <v-card-text>
      <highstock :options="options" :ref="data.name"></highstock>
    </v-card-text>
  </v-card>
</template>

<script>
  //import EventBus from '../EventBus.js'
  export default {
    name: 'chart',
    props: ['data'],
    data () {
      return {
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
              //afterSetExtremes: this.afterSetExtremes
            },
            minRange: 3600 * 1000
          },
          yAxis: {
            floor: 0
          },
          tooltip: {
              pointFormat: '{series.name}: <b>{point.y:.2f}</b><br/>',
              split: true,
              shared: false
          },
          credits: false,
          series: []
        }
      }
    },
    methods: {
      createChart() {
        console.log(this.data)
        for (let [key, value] of Object.entries(this.data.values)) {
          axios.get('/api/project/' + this.$route.params.project + '/' + this.data.slug + '/' + value)
          .then(({data}) => this.$refs[this.data.name].chart.addSeries({ name: value, data }))
          .catch(error => console.log(error))
        }
      }
    },
    mounted() {
      this.createChart()
    }
  }
</script>
