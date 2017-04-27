<template>
    <div v-if="project">
      <v-alert error v-if="!project.projectkey" v-bind:value="true" class="nomargin elevation-1">
        There is no key set for this project, there will be no data collected.
      </v-alert>
      <div class="padding">
        <router-view></router-view>
      </div>
      <v-bottom-nav absolute value="true" class="transparent">
        <v-btn flat light class="teal--text" router :href="baseslug" exact>
          <span>Dashboard</span>
          <v-icon>home</v-icon>
        </v-btn>
        <v-btn flat light class="teal--text" router :href="baseslug + 'settings'">
          <span>Settings</span>
          <v-icon>settings</v-icon>
        </v-btn>
      </v-bottom-nav>
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
export default {
  data () {
    return {
      project: null,
      baseslug: '/project/' + this.$route.params.project + '/'
    }
  },
  mounted () {
    this.fetchProject()
    this.$parent.toolbarItems = true
  },
  watch: {
    '$route' (to, from) {
      if (to.params.project != from.params.project) {
        this.baseslug = '/project/' + to.params.project + '/'
        this.fetchProject()
      }
    }
  },
  methods: {
    fetchProject() {
      this.error = this.project = null
      this.loading = true
      axios.get('/api/project/' + this.$route.params.project)
      .then(({data}) => this.project = data)
      .catch((error) => this.error = error)
    }
  }
}
</script>

<style lang="scss">
  .container.container--fluid,
  .loading-div {
    width: 100%;
    height: 100%;
  }
  .loading-div {
    display: flex;
    justify-content: center;
    align-items: center;
  }
  .bottom-nav {
    width: calc( 100% - 300px );
  }
</style>
