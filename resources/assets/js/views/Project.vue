<template>
    <div v-if="project">
    <v-alert error v-bind:value="!project.projectkey" class="mt-0 elevation-1">
      There is no key set for this project, there will be no data collected.
    </v-alert>
      <v-layout row wrap>
        <v-flex xs12 lg3 mb-4 order-lg2>
          <status></status>
        </v-flex>
        <v-flex xs12 lg9 order-lg1>
          <v-layout row wrap v-if="renderCharts">
            <v-flex xs12 sm6 mb-4 v-for="(graph, i) in graphData" :key="i">
              <chart :data="graph"></chart>
            </v-flex>
          </v-layout>
        </v-flex>
      </v-layout>
    </div>
    <div v-else class="loading-div">
      <v-progress-circular
        indeterminate
        v-bind:size="50"
        class="primary--text"
      />
    </div>
</template>
<script>
import { EventBus } from '../EventBus'
export default {
  components: {
    'chart': require('../components/Chart'),
    'status': require('../components/Status')
  },
  data () {
    return {
      project: null,
      baseslug: '/project/' + this.$route.params.project + '/',
      apiurl: '/api/project/' + this.$route.params.project + '/',
      renderCharts: false,
      graphData: {
        'hddram': {
          title: 'Usage',
          name: 'hddram',
          values: ['hdd', 'ram', 'cpu'],
          slug: 'usage'
        },
        'network': {
          title: 'Network',
          name: 'network',
          values: ['rx', 'tx'],
          slug: 'usage'
        },
        'tables': {
          title: 'Database',
          name: 'tables',
          values: [],
          slug: 'tables'
        },
        'latency': {
          title: 'Latency',
          name: 'latency',
          values: ['page'],
          slug: 'usage'
        }
      },
    }
  },
  mounted () {
    this.fetchProject()
  },
  watch: {
    '$route' (to, from) {
      if (to.params.project != from.params.project) {
        this.baseslug = '/project/' + to.params.project + '/'
        this.apiurl = '/api' + this.baseslug
        this.fetchProject()
      }
    }
  },
  methods: {
    fetchProject() {
      this.renderCharts = false
      axios.get('/api/project/' + this.$route.params.project)
      .then(({data}) => {
        this.project = data
        // Change the toolbar title
        EventBus.$emit('toolbar-title', this.project.name)

        // First, get table data
        axios.get(this.apiurl + 'tables?top=4')
        .then(({data}) => {
          this.graphData.tables.values = data
          this.renderCharts = true
        })
        .catch(error => console.log(error))
      })
      .catch((error) => this.error = error)
    }
  }
}
</script>
