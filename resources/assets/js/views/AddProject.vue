<template>
  <v-stepper v-model="stepper">
    <v-stepper-header>
      <v-stepper-step step="1" :complete="stepper > 1">Project Info</v-stepper-step>
      <v-divider></v-divider>
      <v-stepper-step step="2" :complete="stepper > 2">Set up plugin</v-stepper-step>
      <v-divider></v-divider>
      <v-stepper-step step="3" :complete="stepper > 3">Configuration</v-stepper-step>
      <v-divider></v-divider>
      <v-stepper-step step="4">After setup</v-stepper-step>
    </v-stepper-header>
    <v-stepper-content step="1">
      <h4>Project Info</h4>
      <v-layout row wrap>
        <v-flex sm4 hidden-xs-only>
          <v-subheader>
            Project Name
          </v-subheader>
        </v-flex>
        <v-flex xs12 sm8>
          <v-text-field
            name="project-name"
            label="Project Name"
            id="project-name"
            v-model="project.name"
          ></v-text-field>
        </v-flex>
        <v-flex sm4 hidden-xs-only>
          <v-subheader>
            Project URL
          </v-subheader>
        </v-flex>
        <v-flex xs12 sm8>
          <v-text-field
            name="project-url"
            label="Project URL"
            id="project-url"
            v-model="project.url"
          ></v-text-field>
        </v-flex>
      </v-layout>
      <v-btn primary @click.native="verifyName()" light>Continue</v-btn>
    </v-stepper-content>
    <v-stepper-content step="2">
      <h4>Install plugin</h4>
      <ol>
        <li>Install the WP Data Push plugin on the remote server</li>
        <li>Log in via SSH and go to your webroot</li>
        <li>Activate the plugin: <kbd>wp plugin activate wp-data-push</kbd></li>
        <li>Set the remote host: <kbd>wp ai-client set-remote {{ webUrl() }}/</kbd></li>
        <li>Set the name for the project (<code>{{project.slug}}</code>): <kbd>wp ai-client set-name {{ project.slug }}</kbd></li>
      </ol>
      <v-btn primary @click.native="stepper = 3" light>Continue</v-btn>
      <v-btn flat dark @click.native="stepper = 1">Back</v-btn>
    </v-stepper-content>
    <v-stepper-content step="3">
      <h4>Final configuration</h4>
      <p>
        Create a new key, either by generating one or creating one yourself.</p>
      <p>
        <b>Generate key:</b> <br/>
        <kbd>wp ai-client generate-key</kbd>
      </p>
      <p>
        <b>Create your own key:</b><br/>
        <kbd>wp ai-client set-key "[keyphrase]"</kbd>
      </p>
      <p>
        Enter the key in the field below
      </p>
      <v-layout row wrap>
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
            v-model="project.key"
          ></v-text-field>
        </v-flex>
        <v-flex xs12 mb-3>
          <v-btn primary @click.native="addProject()" light>Add project</v-btn>
          <v-btn flat dark @click.native="stepper = 2" v-if="showBackButton">Back</v-btn>
        </v-flex>
      </v-layout>
    </v-stepper-content>
    <v-stepper-content step="4">
      <h4>Cron job</h4>
      <p>
        After adding the project, set up a cronjob using the following commands:
      </p>
      <ol>
        <li>Generate the command: <kbd>echo "* * * * * cd $(pwd); $(which wp) ai-client send > /dev/null 2>&1"</kbd></li>
        <li>Copy the result</li>
        <li>Edit your cron jobs: <kbd>crontab -e</kbd> and past the previous result</li>
        <li>Save the crontab</li>
      </ol>
    </v-stepper-content>
    <v-snackbar
        :timeout="3000"
        top right
        v-model="snackbar.show"
      >
        {{ snackbar.text }}
        <v-btn flat class="pink--text" @click.native="snackbar.show = false">Close</v-btn>
      </v-snackbar>
  </v-stepper>
</template>

<script>
  import { EventBus } from '../EventBus'
  export default {
    data() {
      return {
        stepper: 1,
        showBackButton: false,
        project: {
          name: '',
          slug: '',
          url: '',
          key: ''
        },
        snackbar: {
          show: false,
          text: ''
        }
      }
    },
    methods: {
      verifyName() {
        if (this.project.name) {
          axios.post('/api/slug', { name: this.project.name })
          .then(({data}) => {
            this.project.slug = data
            this.stepper = 2
          })
        } else {
          this.triggerToast("Invalid project name")
        }
      },
      webUrl() {
        let pathArray = location.href.split( '/' );
        return pathArray[0] + '//' + pathArray[2];
      },
      addProject() {
        axios.post('/api/add', this.project)
        .then(({data}) => {
          EventBus.$emit('refresh-sidebar')
          this.stepper = 4
          this.triggerToast('Project has been created')
        })
      },
      triggerToast(text) {
        this.snackbar.text = text
        this.snackbar.show = true
      }
    },
    mounted() {
      EventBus.$emit('toolbar-settings', {
        title: "Add new project",
        icons: []
      })
    }
  }
</script>
