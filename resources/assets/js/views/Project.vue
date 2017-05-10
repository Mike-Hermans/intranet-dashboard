<template>
    <div v-if="project">
      <v-alert error v-bind:value="!project.projectkey" class="nomargin elevation-1">
        There is no key set for this project, there will be no data collected.
      </v-alert>
      <div class="padding">
        <v-row>
          <v-col xs12 lg9>
            <v-row v-if="renderCharts">
              <v-col lg6 v-for="(graph, i) in graphData" :key="i">
                <chart :data="graph"></chart>
              </v-col>
            </v-row>
          </v-col>
          <v-col lg3>
            <v-card>
              <v-tabs id="tabs" grow icons>
                <v-tab-item href="#tabs-1" slot="activators">
                  WordPress
                  <v-icon>phone</v-icon>
                </v-tab-item>
                <v-tab-item href="#tabs-2" slot="activators">
                  Server
                  <v-icon>favorite</v-icon>
                </v-tab-item>
                <v-tab-item href="#tabs-3" slot="activators">
                  Events
                  <v-icon>phone</v-icon>
                </v-tab-item>
                <v-tab-content :id="'tabs-1'" slot="content">
                  <v-card-text v-if="status">
                    <v-card-row height="60px">
                      <v-icon class="mr-3">info_outline</v-icon>
                      <div>
                        <div>WordPress Version</div><strong>{{this.status.wp}}</strong>
                      </div>
                    </v-card-row>
                  </v-card-text>
                </v-tab-content>
                <v-tab-content :id="'tabs-2'" slot="content">
                  <v-card-text v-if="status">
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
                </v-tab-content>
                <v-tab-content :id="'tabs-3'" slot="content">
                  <v-card-text v-if="status">
                    <v-card-row height="60px">
                      <v-icon class="mr-3">info_outline</v-icon>
                      <div>
                        <div>No recent events</div>
                      </div>
                    </v-card-row>
                  </v-card-text>
                </v-tab-content>
              </v-tabs>
            </v-card>
          </v-col>
        </v-row>
      </div>
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
    'chart': require('../components/Chart')
  },
  data () {
    return {
      project: null,
      baseslug: '/project/' + this.$route.params.project + '/',
      apiurl: '/api/project/' + this.$route.params.project + '/',
      status: null,
      renderCharts: false,
      graphData: [
        {
          title: 'Usage',
          name: 'hddram',
          values: ['hdd', 'ram'],
          slug: 'usage'
        },
        {
          title: 'Network',
          name: 'network',
          values: ['rx', 'tx'],
          slug: 'usage'
        },
        {
          title: 'Latency',
          name: 'latency',
          values: ['page'],
          slug: 'usage'
        }
      ],
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
          this.graphData.push({
            title: 'Tables',
            name: 'tables',
            values: data,
            slug: 'tables'
          })
          this.renderCharts = true
        })
        .catch(error => console.log(error))

        // Get project status
        axios.get(this.apiurl + 'status')
        .then(({data}) => this.status = data)
        .catch(error => console.log(error))
      })
      .catch((error) => this.error = error)
    }
  }
}
</script>
