<template>
    <v-toolbar fixed>
      <v-toolbar-side-icon light @click.native.stop="$parent.nav = !$parent.nav" />
      <v-toolbar-title>{{ title }}</v-toolbar-title>
      <v-toolbar-items>
        <v-toolbar-item ripple v-for="(icon, i) in icons" :key="i" :to="icon.url">
          <v-icon light>{{ icon.icon }}</v-icon>
        </v-toolbar-item>
      </v-toolbar-items>
    </v-toolbar>
</template>

<script>
import { EventBus } from '../EventBus.js'
export default {
  data () {
    return {
      title: 'Dashboard',
      icons: []
    }
  },
  mounted() {
    EventBus.$on('toolbar-settings', settings => {
      this.title = settings.title
      if (settings.icons) {
        this.icons = settings.icons
      } else {
        this.icons = []
      }
    })
  }
}
</script>
