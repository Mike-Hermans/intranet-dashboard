<template>
  <v-card>
    <v-card-text>
      <div> Last update: {{viewedTime | moment('MMMM Do YYYY, H:mm')}} </div>
    </v-card-text>
  </v-card>
</template>

<script>
  import { EventBus } from '../EventBus'
  export default {
    name: 'chart',
    props: ['time'],
    data() {
      return {
        viewedTime: ''
      }
    },
    mounted() {
      this.viewedTime = this.time
      EventBus.$on('chart-setdate', (timestamp) => this.viewedTime = timestamp)
    },
    methods: {

    }
  }
</script>
