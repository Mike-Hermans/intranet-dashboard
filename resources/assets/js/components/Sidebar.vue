<template>
  <v-navigation-drawer dark :mini-variant.sync="mini" persistent v-model="$parent.nav">
    <v-list>
      <!-- Group item -->
      <template v-for="(item,i) in itemGroup">
        <template v-if="item.items">
          <v-list-group v-model="addProjectGroupActive">
            <!-- group header -->
            <v-list-item slot="item">
              <v-list-tile ripple>
                <v-list-tile-action>
                  <v-icon light v-if="item.icon">{{ item.icon }}</v-icon>
                </v-list-tile-action>
                <v-list-tile-title v-text="item.title" />
                <v-list-tile-action>
                  <v-icon light>keyboard_arrow_down</v-icon>
                </v-list-tile-action>
              </v-list-tile>
            </v-list-item>
          </v-list-group>
        </template>

        <!-- single header -->
        <template v-else-if="item.header">
          <v-subheader v-text="item.header" light />
        </template>

        <!-- divider -->
        <template v-else-if="item.divider">
          <v-divider light />
        </template>

        <!-- normal item -->
        <template v-else>
          <v-list-item>
            <v-list-tile :href="item.href" router ripple>
              <v-list-tile-action>
                <v-icon v-if="item.isworking === 0" class="red--text">{{item.icon}}</v-icon>
                <v-icon v-else light>{{item.icon}}</v-icon>
              </v-list-tile-action>
              <v-list-tile-title v-if="item.isworking === 0" class="red--text" v-text="item.title"></v-list-tile-title>
              <v-list-tile-title v-else v-text="item.title" />
            </v-list-tile>
          </v-list-item>
        </template>
      </template>
    </v-list>
  </v-navigation-drawer>
</template>

<script>
import { EventBus } from '../EventBus'
export default {
  data() {
    return {
      defaultItemGroup: [
        { header: 'General' },
        { title: 'Home', href: '/', icon: 'home' },
        { title: 'Neural Network', href: '/network', icon: 'device_hub' },
        { title: 'Add Project', href:'/add-project', icon: 'library_add' },
        { divider: true },
        { header: 'Projects' }
      ],
      itemGroup: null,
      newProjectName: '',
      addProjectGroupActive: false,
      mini: false
    }
  },
  mounted() {
    this.createItemGroup()
    EventBus.$on('refresh-sidebar', this.createItemGroup)
  },
  methods: {
    createItemGroup( data ) {
      axios.get('/api/projects')
      .then(({data}) => {
        let group = JSON.parse(JSON.stringify(this.defaultItemGroup))
        for (let item of data) {
          group.push({
            title: item.name,
            href: '/project/' + item.slug,
            icon: 'timeline',
            isworking: item.isworking
          })
        }
        this.itemGroup = group
      })
    },
    addProject() {
      let querystring = require('querystring')
      let projectName = this.newProjectName
      axios.post('/api/add', querystring.stringify({
          'name': projectName
      }))
      .then(({data}) =>
        this.itemGroup.push({title: projectName, href: '/project/' + data, icon: 'timeline'})
      )
      this.addProjectGroupActive = false
      this.newProjectName = ''
    }
  }
}
</script>

<style lang="scss">
.list__tile.containsinput {
  height: 100px;
}
</style>
