<template>
  <v-snackbar
      :timeout="3000"
      top right
      v-model="show"
    >
    {{ message }}
    <v-btn flat class="pink--text" @click.native="show = false">Close</v-btn>
  </v-snackbar>
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
    EventBus.$on('global-notify', message => {
      this.message = message
      this.show = true
    })
  }
}
</script>
