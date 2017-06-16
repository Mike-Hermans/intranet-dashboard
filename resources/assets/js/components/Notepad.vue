<template>
  <v-card>
    <v-card-row class="blue darken-1">
      <v-card-title>
        <span class="white--text">Notes</span>
      </v-card-title>
    </v-card-row>
    <v-card-text style="height: 100%">
      <v-text-field
        label="Project notes"
        v-model="notes"
        multi-line
        single-line
      ></v-text-field>
    </v-card-text>
  </v-card>
</template>

<script>
  export default {
    data() {
      return {
        notes: ''
      }
    },
    methods: {
      saveNotes() {

      }
    }
  }
</script>

<style lang="scss" scoped>
.card {
  height: 100% !important;
}
</style>
