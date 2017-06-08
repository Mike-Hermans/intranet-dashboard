<template>
  <div>
    <v-layout row wrap>
      <v-flex xs12 lg6>
        <h3>Project Settings</h3>
        <v-layout row>
          <v-flex xs12>
            <v-card-text>
              <v-layout row wrap>
                <v-flex xs12 sm6>
                  <v-checkbox
                    label="Overwrite Project Key"
                    v-model="settings.allowEditProjectKey"
                  ></v-checkbox>
                </v-flex>
                <v-flex xs12 sm6>
                  <v-checkbox
                    label="Overwrite Project Slug"
                    v-model="settings.allowEditProjectSlug"
                  ></v-checkbox>
                </v-flex>
              </v-layout>
            </v-card-text>
          </v-flex>
        </v-layout>
        <v-layout row>
          <v-flex sm4 hidden-xs-only>
            <v-subheader>
              Project Key
            </v-subheader>
          </v-flex>
          <v-flex xs12 sm8>
            <v-text-field
              name="project-key"
              label="Project Key"
              id="project-key"
              :disabled="!settings.allowEditProjectKey"
              v-model="settings.projectkey"
            ></v-text-field>
          </v-flex>
        </v-layout>
        <v-layout row>
          <v-flex sm4 hidden-xs-only>
            <v-subheader>
              Project Slug
            </v-subheader>
          </v-flex>
          <v-flex xs12 sm8>
            <v-text-field
              name="project-slug"
              label="Project Slug"
              id="project-slug"
              :disabled="!settings.allowEditProjectSlug"
              v-model="settings.slug"
            ></v-text-field>
          </v-flex>
        </v-layout>
        <v-layout row>
          <v-flex xs12 sm4 hidden-xs-only>
            <v-subheader>
              Display Name
            </v-subheader>
          </v-flex>
          <v-flex xs12 sm8>
            <v-text-field
              name="display-name"
              label="Display Name"
              id="display-name"
              v-model="settings.name"
            ></v-text-field>
          </v-flex>
        </v-layout>
        <v-layout row>
          <v-flex xs12 sm4 hidden-xs-only>
            <v-subheader>
              Project URL
            </v-subheader>
          </v-flex>
          <v-flex xs12 sm8>
            <v-text-field
              name="project-url"
              label="Project URL"
              id="project-url"
              v-model="settings.url"
            ></v-text-field>
          </v-flex>
        </v-layout>
      </v-flex>
      <!-- <v-flex xs12 lg6>
        <h3>Forecast Settings</h3>
        <v-layout row>
          <v-flex xs12>
            <v-subheader>Types</v-subheader>
            <v-layout row wrap>
              <v-flex xs6 sm4>
                <v-checkbox label="Usage: CPU" v-model="settings.forecast.types" value="cpu" dark></v-checkbox>
              </v-flex>
              <v-flex xs6 sm4>
                <v-checkbox label="Usage: RAM" v-model="settings.forecast.types" value="ram" dark></v-checkbox>
              </v-flex>
              <v-flex xs6 sm4>
                <v-checkbox label="Usage: HDD" v-model="settings.forecast.types" value="hdd" dark></v-checkbox>
              </v-flex>
              <v-flex xs6 sm4>
                <v-checkbox label="Network: RX" v-model="settings.forecast.types" value="rx" dark></v-checkbox>
              </v-flex>
              <v-flex xs6 sm4>
                <v-checkbox label="Network: TX" v-model="settings.forecast.types" value="tx" dark></v-checkbox>
              </v-flex>
              <v-flex xs6 sm4>
                <v-checkbox label="Latency" v-model="settings.forecast.types" value="latency" dark></v-checkbox>
              </v-flex>
            </v-layout>
          </v-flex> -->
        </v-layout>
      </v-flex>
    </v-layout>
    <v-layout row>
      <v-btn
        light
        :loading="saveButtonShowLoading"
        @click.native="saveSettings()"
        :disabled="saveButtonShowLoading"
        class="blue darken-1"
      >
        Save Settings
      </v-btn>
      <v-dialog v-model="removeProjectDialog">
        <v-btn light class="red darken-1" slot="activator">Delete project</v-btn>
        <v-card>
          <v-card-row>
            <v-card-title>Delete project?</v-card-title>
          </v-card-row>
          <v-card-row>
              <v-card-text>
                This will remove all data belonging to this project and can not be undone.
                To verify deletion, enter the project slug below <br>
                (<code>{{project.slug}}</code>)
                <v-flex xs12>
                  <v-text-field
                    name="remove-project-field"
                    label="Project Slug"
                    id="remove-project-field"
                    v-model="removeProjectField"
                  ></v-text-field>
                </v-flex>
              </v-card-text>
          </v-card-row>
          <v-card-row actions>
            <v-btn class="blue--text darken-1" flat="flat" @click.native="removeProjectDialog = false">Cancel</v-btn>
            <v-btn class="red--text darken-1" flat="flat" @click.native="removeProject()">Delete</v-btn>
          </v-card-row>
        </v-card>
      </v-dialog>
    </v-layout>
    <v-snackbar
        :timeout="3000"
        top right
        v-model="toast.show"
      >
      {{ toast.text }}
      <v-btn flat class="pink--text" @click.native="toast.show = false">Close</v-btn>
    </v-snackbar>
  </div>
</template>

<script>
import { EventBus } from '../EventBus'
export default {
  data () {
    return {
      project: {
        slug: ''
      },
      projecturl: '/project/' + this.$route.params.project + '/',
      apiurl: '/api/project/' + this.$route.params.project + '/settings',
      saveButtonShowLoading: false,
      settings: {
        allowEditProjectKey: false,
        allowEditProjectSlug: false,
        projectkey: "",
        name: "",
        slug: "",
        url: "",
        forecast: {
          values: 5000,
          method: 0,
          points: 12,
          sensitivity: 10,
          refresh: 60,
          types: ['cpu']
        }
      },
      toast: {
        show: false,
        text: ''
      },
      removeProjectDialog: false,
      removeProjectField: ''
    }
  },
  methods: {
    getProject() {
      axios.get('/api/project/' + this.$route.params.project)
      .then(({data}) => {
        if (data == 'not_found') {
          this.$router.push('/')
        }
        this.project = data
        this.settings.projectkey = data.projectkey
        this.settings.name = data.name
        this.settings.slug = data.slug
        this.settings.url = data.url
        this.saveButtonShowLoading = false;
        EventBus.$emit('toolbar-settings', {
          title: this.project.name + " - Settings" ,
          icons: [
            {
              icon: 'clear',
              url: '/#/project/' + this.project.slug
            }
          ]
        })
      })
    },
    saveSettings() {
      this.saveButtonShowLoading = true
      axios.post('api/project/' + this.project.slug + '/update', this.settings)
      .then(({data}) => {
        this.saveButtonShowLoading = false
        if (data == 200) {
          EventBus.$emit('refresh-sidebar')
          this.triggerToast('Settings have been saved.')
          if (this.settings.allowEditProjectSlug) {
            this.$router.push('/settings/' + this.settings.slug)
          }
          this.getProject()
        }
      })
      .catch(error => console.log(error))
    },
    triggerToast(text) {
      this.toast.text = text
      this.toast.show = true
    },
    removeProject() {
      if (this.project.slug != this.removeProjectField) {
        this.triggerToast('Project slug is incorrect')
      } else {
        this.removeProjectDialog = false
        axios.post('api/project/remove', this.project)
        .then(({data}) => {
          if (data == 200) {
            EventBus.$emit('refresh-sidebar')
            this.$router.push('/')
          } else {
            this.triggerToast('An error occured when removing the project.')
          }
        })
      }
    }
  },
  mounted() {
    this.getProject()
  }
}
</script>
