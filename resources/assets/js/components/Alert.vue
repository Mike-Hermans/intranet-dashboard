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
      EventBus.$emit('refresh-sidebar')
    })
    EventBus.$on('global-alert-none', () => {
      this.show = false
      EventBus.$emit('refresh-sidebar')
    })
  },
  watch: {
    '$route' (to, from) {
      this.show = false
    }
  },
}
</script>
