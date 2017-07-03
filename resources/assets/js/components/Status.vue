<template>
  <v-card>
    <v-tabs id="tabs" grow icons light>
      <v-tabs-bar slot="activators">
        <v-tabs-slider></v-tabs-slider>
        <v-tabs-item href="#tabs-1">
          WordPress
          <v-icon>public</v-icon>
        </v-tabs-item>
        <v-tabs-item href="#tabs-2">
          Server
          <v-icon>dns</v-icon>
        </v-tabs-item>
        <v-tabs-item href="#tabs-3">
          Plugins
          <v-icon>power</v-icon>
        </v-tabs-item>
      </v-tabs-bar>
      <v-tabs-content :id="'tabs-1'">
        <v-card-text v-if="status">
          <v-card-row height="60px">
            <v-icon class="mr-3">info_outline</v-icon>
            <div>
              <div>WordPress Version</div><strong>{{this.status.wp}}</strong>
            </div>
          </v-card-row>
        </v-card-text>
        <v-divider v-if="events"></v-divider>
        <v-subheader v-if="$parent.time != 'now'">Time relative to: {{ $parent.time | moment("dddd, MMMM Do YYYY, HH:mm:ss") }}</v-subheader>
        <v-card-text v-for="(event, i) in events" :key="i" height="60px">
          <div @click.native='setTime(event.timestamp * 1000)'>
            <div>{{ event.event }}</div>
            <strong>{{ event.timestamp * 1000 | moment("from", $parent.time) }}</strong>
          </div>
        </v-card-text>
      </v-tabs-content>
      <v-tabs-content :id="'tabs-2'">
        <v-card-text v-if="status">
          <v-card-row height="60px">
            <v-icon class="mr-3">dvr</v-icon>
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
            <v-icon class="mr-3">access_time</v-icon>
            <div>
              <div>Running since</div><strong>{{ this.status.up | moment("MMM Do YYYY, HH:mm:ss")}}</strong>
            </div>
          </v-card-row>
          <v-card-row height="60px">
            <v-icon class="mr-3">memory</v-icon>
            <div>
              <div>Total RAM memory</div><strong>{{ Math.round(this.status.mem / 1000) }}MB</strong>
            </div>
          </v-card-row>
          <v-card-row height="60px">
            <v-icon class="mr-3">storage</v-icon>
            <div>
              <div>Total disk space</div><strong>{{ Math.round(this.status.disk / 1000000000) }}GB</strong>
            </div>
          </v-card-row>
        </v-card-text>
      </v-tabs-content>
      <v-tabs-content :id="'tabs-3'">
        <v-card-text v-if="plugins">
          <v-card-row
          height="60px"
          v-for="(plugin, i) in plugins"
          :key="i">
            <v-icon class="mr-3" v-if="plugin.active">power</v-icon>
            <v-icon class="mr-3" v-else>remove</v-icon>
            <div>
              <div>{{ plugin.name }}</div>
              <strong>Version: {{ plugin.version }} </strong>
              <strong v-if="plugin.new_version"> ({{ plugin.new_version }})</strong>
            </div>
          </v-card-row>
        </v-card-text>
      </v-tabs-content>
    </v-tabs>
  </v-card>
</template>

<script>
  import { EventBus } from '../EventBus'
  export default {
    name: 'status',
    data() {
      return {
        status: null,
        plugins: null,
        events: null,
        statusitems: ['status', 'plugins', 'events']
      }
    },
    methods: {
      getData() {
        for (let item of this.statusitems) {
          axios.get(this.$parent.apiurl + item)
          .then(({data}) => {
            this[item] = data
            if (item == 'events') {
              EventBus.$emit('chart-set-flags', {
                icon: 'E',
                events: data
              })
            }
          })
          .catch(error => console.log(error))
        }
      },
      updateData(data) {
        if (data.update_status) {
          this.getData()
        } else {
          for (let [i, event] of Object.entries(this.events)) {
            event.timestamp++
            event.timestamp--
          }
        }
      },
      setTime(timestamp) {
        EventBus.$emit('chart-setdate', timestamp)
      }
    },
    mounted() {
      this.getData()
      EventBus.$on('project-update', (data) => this.updateData(data))
    },
    watch: {
      '$route' (to, from) {
        if (to.params.project != from.params.project) {
          this.getData()
        }
      }
    }
  }
</script>

<style lang="scss" scoped>
</style>
