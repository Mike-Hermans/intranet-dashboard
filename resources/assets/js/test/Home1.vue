<template>
  <div class="pt-2 pl-2 pr-2">
    <v-layout>
      <v-flex xs3 mb-3 v-for="(project, i) in projects" :key="i">
        <v-card>
          <v-card-row style="height: 100px">
            <v-card-title>
              {{ project }}
            </v-card-title>
          </v-card-row>
        </v-card>
      </v-flex>
    </v-layout>
  </div>
</template>

<script>
  export default {
    data() {
      return {
        projects: [
          'Level Level',
          'Level Level Staging',
          'Kansfonds',
          'Kansfonds Staging',
          'Wetlands',
          'Wetlands Staging',
          'Bussiness Insider',
          'Bussiness Insider Staging',
          'KVGO',
          'KVGO Staging',
          'NicOud',
          'NicOud Staging'
        ]
      }
    }
  }
</script>
