<template>
  <div>
    <v-layout row wrap>
      <v-flex xs12 sm6 md4 lg3 mb-4 v-for="(project, i) in projects" :key="i">
        <v-card>
          <v-card-row class="blue darken-2 light">
            <v-card-title>
              <span class="white--text">
                {{ project.name }}
              </span>
            </v-card-title>
          </v-card-row>
          <v-card-row v-if="project.data">
            <v-card-text>
              <v-card-row height="75px">
                <v-icon v-if="project.status.code == 0" class="mr-3 red--text">error</v-icon>
                <v-icon v-if="project.status.code == 1" class="mr-3 orange--text">error</v-icon>
                <v-icon v-if="project.status.code == 2" class="mr-3 green--text">check_circle</v-icon>
                <v-icon v-if="project.status.code == 3" class="mr-3">help</v-icon>
                <div>
                  <div><strong>{{ project.status.message }}</strong></div>
                  <div>{{ project.last_updated * 1000 | moment('from') }}</div>
                </div>
              </v-card-row>
            </v-card-text>
          </v-card-row>
          <v-card-row v-if="project.data">
            <v-card-text>
              <v-layout row>
                <v-flex xs6 v-if="project.data.page">
                  <v-layout row wrap>
                    <v-flex xs12 class="text-xs-center"><v-icon>access_time</v-icon></v-flex>
                    <v-flex xs12 class="text-xs-center"><strong>{{ project.data.page }}s</strong></v-flex>
                  </v-layout>
                </v-flex>
                <v-flex xs6 v-if="project.data.ram">
                  <v-layout row wrap>
                    <v-flex xs12 class="text-xs-center"><v-icon>memory</v-icon></v-flex>
                    <v-flex xs12 class="text-xs-center"><strong>{{ project.data.ram }}%</strong></v-flex>
                  </v-layout>
                </v-flex>
                <v-flex xs6 v-if="project.data.cpu">
                  <v-layout row wrap>
                    <v-flex xs12 class="text-xs-center"><v-icon>memory</v-icon></v-flex>
                    <v-flex xs12 class="text-xs-center"><strong>{{ project.data.cpu }}%</strong></v-flex>
                  </v-layout>
                </v-flex>
                <v-flex xs6 v-if="project.data.hdd">
                  <v-layout row wrap>
                    <v-flex xs12 class="text-xs-center"><v-icon>storage</v-icon></v-flex>
                    <v-flex xs12 class="text-xs-center"><strong>{{ project.data.hdd }}%</strong></v-flex>
                  </v-layout>
                </v-flex>
              </v-layout>
            </v-card-text>
          </v-card-row>
        </v-card>
      </v-flex>
    </v-layout>
  </div>
</template>

<script>
  import { EventBus } from '../EventBus.js'
  export default {
    data() {
      return {
        projects: []
      }
    },
    methods: {
      getProjects() {
        axios.get('/api/projects')
        .then(({data}) => {
          //this.projects = data
          for (let [index, project] of Object.entries(data)) {
            axios.get('/api/project/' + project.slug + '/lastusage')
            .then(({data}) => {
              if (data) {
                if (data.page == -1) {
                  project.status = {
                    code: 0,
                    message: 'Not reachable'
                  }
                } else if (data.hdd > 80 || data.ram > 80 || data.cpu > 80) {
                  project.status = {
                    code: 1,
                    message: 'Unexpected measurements'
                  }
                } else {
                  project.status = {
                    code: 2,
                    message: 'No problems'
                  }
                }
              } else {
                project.status = {
                  code: 3,
                  message: 'No data'
                }
              }
              project.data = data
              this.projects.splice(index, 1, project)
            })
          }
        })
      }
    },
    mounted() {
      EventBus.$emit('toolbar-settings', { title: 'Home' })
      this.getProjects()
    }
  }
</script>
