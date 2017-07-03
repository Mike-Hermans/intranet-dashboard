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
        chart: false,
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
          axios.get(this.$parent.apiurl +  this.data.slug + '/' + value.value)
          .then(({data}) => this.chart.addSeries({
            id: value.value,
            name: value.value,
            color: value.color,
            data
          }))
          .catch(error => console.log(error))

          // Check for forecast values
          axios.get(this.$parent.apiurl + 'forecast/' + value.value)
          .then(({data}) => {
            if (data.length > 0) {
              let serie = []
              let eighty = []
              for (let [key, fpoint] of Object.entries(data)) {
                serie.push([fpoint.point * 1000, fpoint.forecast])
                eighty.push([fpoint.point * 1000, fpoint.lo95, fpoint.hi95])
              }
              this.chart.addSeries({
                id: value.value + '_forecast',
                name: value.value + '_forecast',
                color: '000000',
                data: serie,
                zIndex: 1,
                marker: {
                    enabled: true,
                    fillColor: 'white',
                    lineWidth: 2
                }
              })
              this.chart.addSeries({
                name: '95% Range',
                data: eighty,
                type: 'arearange',
                color: value.color,
                lineWidth: 0,
                linkedTo: value.value + '_forecast',
                fillOpacity: 0.2,
                zIndex: 0
              })
            }
          })
        }
      },
      chartRange(range) {
        if (this.chart !== undefined && this.chart.xAxis !== undefined) {
          if (this.chart.xAxis.length > 0) {
            this.chart.xAxis[0].setExtremes(range.min, range.max, true, false)
          }
        }
      },
      chartTimestamp(timestamp) {
        // For each series, highlight the correct point
        if (this.chart !== undefined) {
          for (let serie of this.chart.series) {
            for (let chartpoint of serie.data) {
              if (chartpoint && chartpoint.x == timestamp) {
                chartpoint.select()
                chartpoint.onMouseOver()
                break;
              }
            }
          }
        }
      },
      chartUpdate(data) {
        if (this.chart !== undefined) {
          let usage = data.usage

          if (this.data.name == 'tables') {
            if (data.tables == null) {
              return
            }
            usage = data.tables
          }

          for (let [i, serie] of Object.entries(this.chart.series)) {
            if (usage[serie.name] !== undefined) {
              this.chart.series[i].addPoint([data.timestamp, usage[serie.name]], false, true)
            }
          }
          // Redraw chart after all values have been updated
          this.chart.redraw()
        }
      },
      setFlags(flags) {
        let data = []
        let used_timestamps = []
        for (let flag of flags.events) {
          // Only allow unique timestamps
          if (!used_timestamps.includes(flag.timestamp) && ((new Date) - flag.timestamp * 1000) < 604800000) {
            data.push({
              x: flag.timestamp * 1000,
              //text: flag.event,
              title: flags.icon
            })
          }
        }
        // Sort based on timestamp
        data.sort((a, b) => b.timestamp - a.timestamp)

        // Add to chart
        this.chart.addSeries({
          type: 'flags',
          shape: 'circlepin',
          width: 16,
          data
        })
      }
    },
    mounted() {
      if (this.$refs[this.data.name] !== undefined) {
        this.chart = this.$refs[this.data.name].chart
        this.createChart()
        EventBus.$on('chart-setdate', (timestamp) => this.chartTimestamp(timestamp))
        EventBus.$on('chart-setrange', (range) => this.chartRange(range))
        EventBus.$on('chart-set-flags', (flags) => this.setFlags(flags))
        EventBus.$on('project-update', (data) => this.chartUpdate(data))
      }
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
