<template>
    <v-toolbar>
      <v-toolbar-side-icon @click.native.stop="$parent.nav = !$parent.nav" />
      <v-toolbar-logo>{{ title }}</v-toolbar-logo>
    </v-toolbar>
</template>

<script>
import { EventBus } from '../EventBus.js'
export default {
  data () {
    return {
      title: 'Dashboard'
    }
  },
  mounted() {
    EventBus.$on('toolbar-title', title => {
      this.title = title
    })
  }
}
</script>
