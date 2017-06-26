<template>
  <v-alert error v-bind:value="show" class="mt-0 elevation-1">
    {{ message }}
  </v-alert>
</template>

<script>
import { EventBus } from '../EventBus.js'
export default {
  data () {
    return {
      message: '',
      show: false
    }
  },
  mounted() {
    EventBus.$on('global-alert', message => {
      this.message = message
      this.show = true
    })
  },
  watch: {
    '$route' (to, from) {
      if (to.params.project != from.params.project) {
        this.show = false
      }
    }
  },
}
</script>
