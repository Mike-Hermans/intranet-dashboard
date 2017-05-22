<template>
  <div>
    <h4>Settings</h4>
    <v-card class="grey lighten-4 elevation-0">
      <v-card-text>
        <v-container fluid>
          <v-layout row>
            <v-flex xs4>
              <v-subheader>Project Key</v-subheader>
            </v-flex>
            <v-flex xs8>
              <v-text-field
                name="project-key"
                label="Key"
                v-model="project.projectkey"
              ></v-text-field>
            </v-flex>
          </v-layout>
        </v-container>
      </v-card-text>
    </v-card>
  </div>
</template>

<script>
  export default {
    data() {
      return {
        project: this.$parent.project
      }
    }
  }
</script>
