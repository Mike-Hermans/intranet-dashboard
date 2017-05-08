<template>
  <div>
    <h4>{{project.name}}</h4>
    <v-row>
      <v-col xs9 class="chartcontainer">
        <v-row>
          <v-col xs6>
            <v-card>
              <v-card-text>
                <highstock :options="initChart('hddram')" ref="hddram"></highstock>
              </v-card-text>
            </v-card>
          </v-col>
          <v-col xs6>
            <v-card>
              <v-card-text>
                <highstock :options="initChart('network')" ref="network"></highstock>
              </v-card-text>
            </v-card>
          </v-col>
          <v-col xs6>
            <v-card>
              <v-card-text>
                <highstock :options="initChart('tables')" ref="tables"></highstock>
              </v-card-text>
            </v-card>
          </v-col>
          <v-col xs6>
            <v-card>
              <v-card-text>
                <highstock :options="initChart('latency')" ref="latency"></highstock>
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
                <div>Running since</div><strong>{{this.status.up}}</strong>
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
      }
    }
  },
  methods: {
    // afterSetExtremes(e) {
    //   let tables = [
    //     ['hdd', 'ram'],
    //     ['rx', 'tx'],
    //     ['page']
    //   ]
    //   let charts = [
    //     this.$refs.hddram.chart,
    //     this.$refs.network.chart,
    //     this.$refs.latency.chart
    //   ]
    //   for (let [i, names] of Object.entries(tables)) {
    //     let chart = charts[i],
    //     seriesOptions = []
    //     for (let [j, name] of Object.entries(names)) {
    //       axios.get(this.apiurl + 'usage/' + name)
    //       .then(({data}) => chart.series[j].setData(data))
    //       .catch(error => console.log(error))
    //     }
    //   }
    //   this.fetchTableData(e)
    // },
    // fetchTableData(e) {
    //   let chart = this.$refs.tables.chart
    //   axios.get(this.apiurl + 'tables?top=4')
    //   .then(({data}) => {
    //     for ( let [i, table] of data.entries() ) {
    //       axios.get(this.apiurl + 'tables/' + table)
    //       .then(({data}) => {
    //         for ( let [i, serie] of chart.series.entries() ) {
    //           if (serie.name == table) {
    //             chart.series[i].setData(data)
    //             continue
    //           }
    //         }
    //       })
    //       .catch(error => console.log(error))
    //     }
    //   })
    // },
    initChart(chart) {
      let options = {
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

      if ( chart == 'hddram' ) {
        options.yAxis.max = 100
      }

      if ( chart !== 'tables' ) {
        for (let [i, name] of Object.entries(this.tableData[chart])) {
          axios.get(this.apiurl + 'usage/' + name)
          .then(({data}) => this.$refs[chart].chart.addSeries({ name, data }))
          .catch(error => console.log(error))
        }
      } else {
        axios.get(this.apiurl + 'tables?top=4')
        .then(({data}) => {
          for (let [i, name] of Object.entries(data) ) {
            axios.get(this.apiurl + 'tables/' + name)
            .then(({data}) => this.$refs.tables.chart.addSeries({ name, data }))
            .catch(error => console.log(error))
          }
        })
      }
      return options
    }
  },
  created() {
    axios.get(this.apiurl + 'status')
    .then(({data}) => this.status = data)
    .catch(error => console.log(error))
  }
}
</script>
