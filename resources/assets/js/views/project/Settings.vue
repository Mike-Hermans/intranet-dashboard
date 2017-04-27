<template>
  <div>
    <h4>Settings</h4>
    <v-card class="grey lighten-4 elevation-0">
      <v-card-text>
        <v-container fluid>
          <v-row row>
            <v-col xs4>
              <v-subheader>Project Key</v-subheader>
            </v-col>
            <v-col xs8>
              <v-text-field
                name="project-key"
                label="Key"
                v-model="project.projectkey"
              ></v-text-field>
            </v-col>
          </v-row>
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
