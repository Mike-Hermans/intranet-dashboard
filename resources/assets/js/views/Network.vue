<template>
  <div>
    <v-layout row>
      <v-flex xs12 xs6>

      </v-flex>
      <v-flex xs12 xs6>
        <v-card>
          <v-card-row class="blue darken-1">
            <v-card-title>
              <span class="white--text">Network Stats</span>
            </v-card-title>
          </v-card-row>
          <v-list subheader>
            <v-subheader>Data</v-subheader>
            <v-list-item>
              <v-list-tile-content>
                <v-list-tile-title>
                  <b>Active projects:</b> {{ activeProjects }}
                </v-list-tile-title>
              </v-list-tile-content>
            </v-list-item>
            <v-list-item>
              <v-list-tile-content>
                <v-list-tile-title>
                  <b>Samples:</b> {{ activeSamples }}
                </v-list-tile-title>
              </v-list-tile-content>
            </v-list-item>
            <v-list-item>
              <v-list-tile-content>
                <v-list-tile-title>
                  <b>Error samples:</b> {{ activeErrorSamples }}
                </v-list-tile-title>
              </v-list-tile-content>
            </v-list-item>
            <v-divider></v-divider>
            <v-subheader>Neural Network Verification</v-subheader>
            <v-list-item v-for="(stat, i) in nnstats" :key="i">
              <v-list-tile-content>
                <v-list-tile-title>
                  <b>{{ i }}:</b> {{ stat }}
                </v-list-tile-title>
              </v-list-tile-content>
            </v-list-item>
          </v-list>
        </v-card>
      </v-flex>
    </v-layout>
  </div>
</template>

<script>
import { EventBus } from '../EventBus'
export default {
  data () {
    return {
      projects: [],
      nnstats: []
    }
  },
  computed: {
    activeProjects() {
      let count = 0
      if (this.projects) {
        for (let project of this.projects) {
          if (project.active) {
            count++
          }
        }
      }
      return count
    },
    activeSamples() {
      let count = 0
      if (this.projects) {
        for (let project of this.projects) {
          if (project.active) {
            count += project.samples
          }
        }
      }
      return count
    },
    activeErrorSamples() {
      return 0
    }
  },
  methods: {
    getStats() {
      axios.get('/api/network/projects')
      .then(({data}) => this.projects = data)
      axios.get('/api/network/nnstats')
      .then(({data}) => this.nnstats = data)
    }
  },
  mounted() {
    this.getStats()
    EventBus.$emit('toolbar-settings', {
      title: "Neural Network"
    })
  }
}
</script>

<style lang="scss" scoped>
.list__item {
  padding: 0 24px;

  .list__tile__content {
    padding: 2px 0;
  }
}
</style>
