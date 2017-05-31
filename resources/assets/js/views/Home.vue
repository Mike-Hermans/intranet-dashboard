<template>
    <div>
        <h3>Homepage</h3>
    </div>
</template>

<script>
  import { EventBus } from '../EventBus.js'
  export default {
    data() {
      return {

      }
    },
    mounted() {
      EventBus.$emit('toolbar-settings', {
        title: 'Home',
        icons: [
          {
            icon: 'settings',
            url: '/#/settings/'
          }
        ]
      })
    }
  }
</script>
