<template>
  <div>
    <h4>{{project.name}}</h4>
    <v-row>
      <v-col xs9 class="chartcontainer">
        <v-row>
          <v-col xs6>
            <v-card>
              <v-card-text>
                <highstock :options="options" ref="hddram"></highstock>
              </v-card-text>
            </v-card>
          </v-col>
          <v-col xs6>
            <v-card>
              <v-card-text>
                <highstock :options="options" ref="network"></highstock>
              </v-card-text>
            </v-card>
          </v-col>
          <v-col xs6>
            <v-card>
              <v-card-text>
                <highstock :options="options" ref="tables"></highstock>
              </v-card-text>
            </v-card>
          </v-col>
          <v-col xs6>
            <v-card>
              <v-card-text>
                <highstock :options="options" ref="latency"></highstock>
              </v-card-text>
            </v-card>
          </v-col>
        </v-row>
      </v-col>
      <v-col xs3>
        <v-card>
          <v-card-row>
            <v-card-title class="blue darken-1">
              <span class="white--text">Status</span>
              <v-spacer></v-spacer>
            </v-card-title>
          </v-card-row>
          <v-card-text v-if="status">
            <v-card-row height="60px">
              <v-icon class="mr-3">info_outline</v-icon>
              <div>
                <div>WordPress Version</div><strong>{{this.status.wp}}</strong>
              </div>
            </v-card-row>
            <v-card-row height="60px">
              <v-icon class="mr-3">info_outline</v-icon>
              <div>
                <div>Operating System</div><strong>{{this.status.os}}</strong>
              </div>
            </v-card-row>
            <v-card-row height="60px">
              <v-icon class="mr-3">info_outline</v-icon>
              <div>
                <div>PHP Version</div><strong>{{this.status.php}}</strong>
              </div>
            </v-card-row>
            <v-card-row height="60px">
              <v-icon class="mr-3">info_outline</v-icon>
              <div>
                <div>Running since</div><strong>{{ this.status.up | moment("MMM Do YYYY, HH:mm:ss")}}</strong>
              </div>
            </v-card-row>
            <v-card-row height="60px">
              <v-icon class="mr-3">info_outline</v-icon>
              <div>
                <div>Total RAM memory</div><strong>{{ Math.round(this.status.mem / 1000) }}MB</strong>
              </div>
            </v-card-row>
            <v-card-row height="60px">
              <v-icon class="mr-3">info_outline</v-icon>
              <div>
                <div>Total disk space</div><strong>{{ Math.round(this.status.disk / 1000000000) }}GB</strong>
              </div>
            </v-card-row>
          </v-card-text>
          <v-card-text v-else>
            <v-progress-circular
              indeterminate
              v-bind:size="50"
              class="primary--text"
            />
          </v-card-text>
        </v-card>
      </v-col>
    </v-row>

  </div>
</template>

<script>
export default {
  data() {
    return {
      project: this.$parent.project,
      apiurl: '/api/project/' + this.$parent.project.slug + '/',
      status: null,
      tableData: {
        'hddram': ['hdd', 'ram'],
        'network': ['rx', 'tx'],
        'latency': ['page']
      },
      graphData: [
        {
          name: 'hddram',
          values: ['hdd', 'ram'],
          slug: 'usage'
        },
        {
          name: 'network',
          values: ['rx', 'tx'],
          slug: 'usage'
        },
        {
          name: 'latency',
          values: ['page'],
          slug: 'usage'
        }
      ],
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
    // afterSetExtremes(e) {
    //   for (let [i, graph] of Object.entries(this.graphData)) {
    //     for (let [j, value] of Object.entries(graph.values)) {
    //       axios.get(this.apiurl + graph.slug + '/' + value)
    //       .then(({data}) => {
    //         for ( let [k, serie] of chart.series.entries() ) {
    //           if (serie.name == table) {
    //             this.$refs[graph.name].chart.series[k].setData(data)
    //             continue
    //           }
    //         }
    //       })
    //       .catch(error => console.log(error))
    //     }
    //   }
    // },
    // syncCharts(e) {
    //   console.log('a')
    //   var chart,
    //     point,
    //     i,
    //     event;
    //     console.log(this.$refs)
    //     for (let [i, graph] of Object.entries(this.$refs)) {
    //         event = graph.chart.pointer.normalize(e.originalEvent); // Find coordinates within the chart
    //         point = graph.chart.series[0].searchPoint(event, true); // Get the hovered point
    //
    //         if (point) {
    //             point.highlight(e);
    //         }
    //     }
    // },
    setChartData() {
      for (let [i, graph] of Object.entries(this.graphData)) {
        for (let [j, value] of Object.entries(graph.values)) {
          axios.get(this.apiurl + graph.slug + '/' + value)
          .then(({data}) => this.$refs[graph.name].chart.addSeries({ name: value, data }))
          .catch(error => console.log(error))
        }
      }
    }
  },
  mounted() {
    axios.get(this.apiurl + 'tables?top=4')
    .then(({data}) => {
      this.graphData.push({
        name: 'tables',
        values: data,
        slug: 'tables'
      })
      this.setChartData();
    })
    .catch(error => console.log(error))
    axios.get(this.apiurl + 'status')
    .then(({data}) => this.status = data)
    .catch(error => console.log(error))

    // this.Highcharts.Pointer.prototype.reset = function () {
    //     return undefined;
    // };
    //
    // /**
    //  * Highlight a point by showing tooltip, setting hover state and draw crosshair
    //  */
    // this.Highcharts.Point.prototype.highlight = function (event) {
    //     this.onMouseOver(); // Show the hover marker
    //     this.series.chart.tooltip.refresh(this); // Show the tooltip
    //     this.series.chart.xAxis[0].drawCrosshair(event, this); // Show the crosshair
    // };
  }
}
</script>
