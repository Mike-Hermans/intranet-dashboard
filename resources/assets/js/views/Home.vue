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
      EventBus.$emit('toolbar-title', 'Status')
    }
  }
</script>
