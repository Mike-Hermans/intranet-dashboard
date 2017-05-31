<template>

</template>

<script>
import { EventBus } from '../EventBus'
export default {
  data () {
    return {

    }
  },
  mounted() {
    EventBus.$emit('toolbar-settings', {
      title: "General Settings" ,
      icons: [
        {
          icon: 'clear',
          url: '/#/'
        }
      ]
    })
  }
}
</script>
