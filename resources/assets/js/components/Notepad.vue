<template>
  <v-card>
    <v-tabs id="tabs-notes" grow icons light>
      <v-tabs-bar slot="activators">
        <v-tabs-slider></v-tabs-slider>
        <v-tabs-item href="#tabs-notes-1">
          General
          <v-icon v-if="generalNote">description</v-icon>
          <v-icon v-else>insert_drive_file</v-icon>
        </v-tabs-item>
        <v-tabs-item href="#tabs-notes-2">
          Timed
          <v-icon v-if="timedNote">description</v-icon>
          <v-icon v-else>insert_drive_file</v-icon>
        </v-tabs-item>
      </v-tabs-bar>
      <v-tabs-content :id="'tabs-notes-1'">
        <v-card-text style="height: 100%">
          <h5>Project notes</h5>
          <v-text-field
            label="Project notes"
            v-model="generalNote"
            multi-line
            single-line
          ></v-text-field>
          <v-btn
            light
            :loading="generalButtonShowLoading"
            @click.native="saveNote('general')"
            :disabled="generalButtonShowLoading"
            class="blue darken-1"
          >
            Save note
          </v-btn>
        </v-card-text>
      </v-tabs-content>
      <v-tabs-content :id="'tabs-notes-2'">
        <v-card-text style="height: 100%">
          <h5 mt-4>Notes for current time</h5>
          <v-text-field
            label="Project notes"
            v-model="timedNote"
            multi-line
            single-line
          ></v-text-field>
          <v-btn
            light
            :loading="timedButtonShowLoading"
            @click.native="saveNote('timed')"
            :disabled="timedButtonShowLoading"
            class="blue darken-1"
          >
            Save note
          </v-btn>
        </v-card-text>
      </v-tabs-content>
    </v-tabs>
  </v-card>
</template>

<script>
import { EventBus } from '../EventBus'
  export default {
    data() {
      return {
        notes: '',
        generalNote: '',
        timedNote: '',
        generalButtonShowLoading: false,
        timedButtonShowLoading: false,
        apiurl: '/api/project/' + this.$route.params.project + '/notes'
      }
    },
    methods: {
      saveNote(type) {
        let note = {}
        if (type == 'timed') {
          this.timedButtonShowLoading = true
          note = {
            note: this.timedNote,
            timestamp: this.$parent.time
          }
        } else {
          this.generalButtonShowLoading = true
          note = { note: this.generalNote }
        }
        axios.post(this.apiurl, note)
        .then(({data}) => {
          if (data == 200) {
            this.timedButtonShowLoading = false
            this.generalButtonShowLoading = false
            EventBus.$emit('notify', 'Note has been saved')
          }
        })
      },
      getNotes() {
        axios.get(this.apiurl)
        .then(({data}) => {
          this.notes = data
          for (let note of data) {
            if (note.timestamp === 0) {
              this.generalNote = note.note
            }
          }
        })
      },
      findNote(timestamp) {
        let foundNote = false;
        for (let note of this.notes) {
          if (note.timestamp * 1000 == timestamp) {
            this.timedNote = note.note
            foundNote = true;
            break;
          }
        }
        if (!foundNote) {
          this.timedNote = ''
        }
      }
    },
    mounted() {
      this.getNotes()
      EventBus.$on('chart-setdate', (timestamp) => this.findNote(timestamp))
    }
  }
</script>

<style lang="scss" scoped>
.card {
  height: 100% !important;
}

.tabs__items {
  border-style: none !important;
}
</style>
