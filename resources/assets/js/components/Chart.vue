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
  import { EventBus } from '../EventBus'
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
              showInNavigator: true,
              cursor: 'pointer',
              events: {
                click(e) {
                  // When a point gets clicked, emit its timestamp
                  EventBus.$emit('chart-setdate', e.point.x)
                }
              }
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
            },
            {
              type: 'all',
              text: '1w'
            }],
            inputEnabled: false, // it supports only days
            selected: 1 // default is 1 day
          },
          xAxis: {
            events: {
              afterSetExtremes(e) {
                // Prevent trigger on chart load
                if (e.trigger != undefined) {
        	        EventBus.$emit('chart-setrange', e)
				        }
              }
            },
            minRange: 3600 * 1000
          },
          yAxis: {
            floor: 0
          },
          tooltip: {
              pointFormat: '{series.name}: <b>{point.y:.2f}</b>' + this.data.suffix + '<br/>',
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
        for (let [key, value] of Object.entries(this.data.values)) {
          axios.get('/api/project/' + this.$route.params.project + '/' + this.data.slug + '/' + value.value)
          .then(({data}) => this.$refs[this.data.name].chart.addSeries({
            id: value.value,
            name: value.value,
            color: value.color,
            data
          }))
          .catch(error => console.log(error))

          // Check for forecast values
          // axios.get('/api/project/' + this.$route.params.project + '/forecast/' + value.value)
          // .then(({data}) => {
          //   if (data !== 'no_forecast') {
          //     let serie = []
          //     let eighty = []
          //     for (let [key, fpoint] of Object.entries(data)) {
          //       serie.push([fpoint.point * 1000, fpoint.forecast])
          //       eighty.push([fpoint.point * 1000, fpoint.lo95, fpoint.hi95])
          //     }
          //     this.$refs[this.data.name].chart.addSeries({
          //       id: value.value + '_forecast',
          //       name: value.value + '_forecast',
          //       color: '000000',
          //       data: serie,
          //       zIndex: 1,
          //       marker: {
          //           enabled: true,
          //           fillColor: 'white',
          //           lineWidth: 2
          //       }
          //     })
          //     this.$refs[this.data.name].chart.addSeries({
          //       name: '95% Range',
          //       data: eighty,
          //       type: 'arearange',
          //       color: value.color,
          //       lineWidth: 0,
          //       linkedTo: value.value + '_forecast',
          //       fillOpacity: 0.2,
          //       zIndex: 0
          //     })
          //   }
          // })
        }
      },
      chartRange(range) {
        let chart = this.$refs[this.data.name].chart
        chart.xAxis[0].setExtremes(range.min, range.max, true, false)
      },
      chartTimestamp(timestamp) {
        let chart = this.$refs[this.data.name].chart
        // For each series, highlight the correct point
        for (let serie of chart.series) {
          for (let chartpoint of serie.data) {
            if (chartpoint && chartpoint.x == timestamp) {
              chartpoint.select()
              chartpoint.onMouseOver()
              break;
            }
          }
        }
      },
      chartUpdate(data) {
        let usage = data.usage
        if (this.data.name == 'tables') {
          if (data.tables == null) {
            return
          }
          usage = data.tables
        }
        let chart = this.$refs[this.data.name].chart
        let lastTimestamp = chart.series[0].data[chart.series[0].data.length -1].x
        let currentTimestamp = data.timestamp

        // Only update when data is actually newer
        if (currentTimestamp > lastTimestamp) {
          for (let [i, serie] of Object.entries(chart.series)) {
            if (usage[serie.name] !== undefined) {
              chart.series[i].addPoint([currentTimestamp, usage[serie.name]], false, true)
            }
          }
          // Redraw chart after all values have been updated
          chart.redraw()
        }
      }
    },
    mounted() {
      this.createChart()
      EventBus.$on('chart-setdate', (timestamp) => this.chartTimestamp(timestamp));
      EventBus.$on('chart-setrange', (range) => this.chartRange(range));
      EventBus.$on('update', (data) => this.chartUpdate(data));
    }
  }
</script>

<style lang="scss" scoped>
.card__title {
  padding-bottom: 0;
}
.card__text {
  padding-top: 0;
}
</style>
