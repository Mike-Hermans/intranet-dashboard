<template>
    <v-toolbar fixed>
      <v-toolbar-side-icon light @click.native.stop="$parent.nav = !$parent.nav" />
      <v-toolbar-title>{{ title }}</v-toolbar-title>
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
