<template>
  <div>
    <v-layout row>
      <v-flex xs12>
        <v-card-text>
          <v-layout row wrap>
            <v-flex xs12 sm6>
              <v-checkbox
                label="Allow overwrite Project Key"
                v-model="settings.allowEditProjectKey"
              ></v-checkbox>
            </v-flex>
            <v-flex xs12 sm6>
              <v-checkbox
                label="Allow overwrite Project Slug"
                v-model="settings.allowEditProjectSlug"
              ></v-checkbox>
            </v-flex>
          </v-layout>
        </v-card-text>
      </v-flex>
    </v-layout>
    <v-layout row v-if="settings.allowEditProjectKey">
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
    <v-layout row v-if="settings.allowEditProjectSlug">
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
          v-model="settings.displayname"
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
    <v-layout row>
      <v-btn
        xs12
        light
        secondary
        :loading="saveButtonShowLoading"
        @click.native="saveSettings()"
        :disabled="saveButtonShowLoading"
        class="blue darken-1"
      >
        Save Settings
      </v-btn>
    </v-layout>
  </div>
</template>

<script>
import { EventBus } from '../EventBus'
export default {
  data () {
    return {
      project: null,
      projecturl: '/project/' + this.$route.params.project + '/',
      apiurl: '/api/project/' + this.$route.params.project + '/settings',
      settings: {
        allowEditProjectKey: false,
        allowEditProjectSlug: false,
        projectkey: "",
        displayname: "",
        slug: "",
        url: ""
      }
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
        this.settings.displayname = data.name
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
          this.$router.push('/project/' + this.settings.slug)
        }
      })
      .catch(error => console.log(error))
    }
  },
  mounted() {
    this.getProject()
  }
}
</script>
