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
                // Divide by 1000 to convert to seconds
                //click: (e) => this.test()
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
              shared: false,

          },
          credits: false,
          series: []
        }
      }
    },
    methods: {
      createChart() {
        for (let [key, value] of Object.entries(this.data.values)) {
          axios.get('/api/project/' + this.$route.params.project + '/' + this.data.slug + '/' + value)
          .then(({data}) => this.$refs[this.data.name].chart.addSeries({ name: value, data }))
          .catch(error => console.log(error))

          if (value == 'cpu') {
            axios.get('/api/project/' + this.$route.params.project + '/forecast/' + value)
            .then(({data}) => {
              let serie = []
              let eighty = []
              for (let [key, fpoint] of Object.entries(data)) {
                serie.push([fpoint.point * 1000, fpoint.forecast])
                eighty.push([fpoint.point * 1000, fpoint.lo95, fpoint.hi95])
              }
              this.$refs[this.data.name].chart.addSeries({
                id: value + '_forecast',
                name: value + '_forecast',
                data: serie,
                zIndex: 1,
                marker: {
                    enabled: true,
                    fillColor: 'white',
                    lineWidth: 2
                }
              })
              this.$refs[this.data.name].chart.addSeries({
                name: '95% Range',
                data: eighty,
                type: 'arearange',
                lineWidth: 0,
                linkedTo: value + '_forecast',
                fillOpacity: 0.3,
                zIndex: 0
              })
            })
          }
        }
      }
    },
    mounted() {
      this.createChart()
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
