<template>
    <div v-if="projects">
        <h3>Homepage</h3>
    </div>
    <div v-else>
      loading..
    </div>
</template>

<script>
  import { EventBus } from '../EventBus.js'
  export default {
    data() {
      return {
        projects: false
      }
    },
    methods: {
      getProjects() {
        axios.get('/api/projects')
        .then(({data}) => this.projects = data)
      }
    },
    mounted() {
      EventBus.$emit('toolbar-settings', { title: 'Home' })
      this.getProjects()
    }
  }
</script>
