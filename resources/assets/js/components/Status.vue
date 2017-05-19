<template>
  <v-card>
    <v-tabs id="tabs" grow icons>
      <v-tab-item href="#tabs-1" slot="activators">
        WordPress
        <v-icon>public</v-icon>
      </v-tab-item>
      <v-tab-item href="#tabs-2" slot="activators">
        Server
        <v-icon>dns</v-icon>
      </v-tab-item>
      <v-tab-item href="#tabs-3" slot="activators">
        Plugins
        <v-icon>power</v-icon>
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
        <v-divider v-if="events"></v-divider>
        <v-card-text v-for="(event, i) in events" :key="i" height="60px">
          <div>
            <div>{{ event.event }}</div>
            <strong>{{ event.timestamp * 1000 | moment("from")}}</strong>
          </div>
        </v-card-text>
      </v-tab-content>
      <v-tab-content :id="'tabs-2'" slot="content">
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
      </v-tab-content>
      <v-tab-content :id="'tabs-3'" slot="content">
        <v-card-text v-if="plugins">
          <v-card-row
          height="60px"
          v-for="(plugin, i) in plugins"
          :key="i">
            <v-icon class="mr-3">power</v-icon>
            <div>
              <div>{{ plugin.name }}</div>
              <strong>Version: {{ plugin.version }}</strong>
            </div>
          </v-card-row>
        </v-card-text>
      </v-tab-content>
    </v-tabs>
  </v-card>
</template>

<script>
  export default {
    name: 'status',
    data() {
      return {
        status: null,
        apiurl: '/api/project/' + this.$route.params.project + '/',
        plugins: null,
        events: null,
        statusitems: ['status', 'plugins', 'events']
      }
    },
    methods: {
      getData() {
        for (let item of this.statusitems) {
          axios.get(this.apiurl + item)
          .then(({data}) => this[item] = data)
          .catch(error => console.log(error))
        }
      }
    },
    mounted() {
      this.getData()
    },
    watch: {
      '$route' (to, from) {
        if (to.params.project != from.params.project) {
          this.apiurl = '/api/project/' + to.params.project + '/'
          this.getData()
        }
      }
    }
  }
</script>

<style lang="scss" scoped>
</style>