<template>
  <v-sidebar v-model="$parent.nav">
    <v-list dense>
      <!-- Group item -->
      <template v-for="(item,i) in itemGroup">
        <template v-if="item.items">
          <v-list-group v-model="addProjectGroupActive">
            <!-- group header -->
            <v-list-item slot="item">
              <v-list-tile ripple>
                <v-list-tile-action>
                  <v-icon>{{item.icon}}</v-icon>
                </v-list-tile-action>
                <v-list-tile-title v-text="item.title" />
                <v-list-tile-action>
                  <v-icon>keyboard_arrow_down</v-icon>
                </v-list-tile-action>
              </v-list-tile>
            </v-list-item>

            <!-- group items -->
            <v-list-item v-for="(subItem,i) in item.items" :key="i">
              <template v-if="subItem.form">
                <v-list-item>
                  <form @submit.prevent="addProject()">
                    <v-list-tile class="containsinput">
                      <v-text-field
                      dark
                      v-model="newProjectName"
                      name="name"
                      label="Project name"
                      ></v-text-field>
                    </v-list-tile>
                  </form>
                </v-list-item>
              </template>
              <v-list-tile v-else ripple>
                <v-list-tile-title v-text="subItem.title" />
              </v-list-tile>
            </v-list-item>
          </v-list-group>
        </template>

        <!-- single header -->
        <template v-else-if="item.header">
          <v-subheader v-text="item.header" />
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
                <v-icon v-if="item.icon">{{ item.icon }}</v-icon>
              </v-list-tile-action>
              <v-list-tile-title v-text="item.title" />
            </v-list-tile>
          </v-list-item>
        </template>
      </template>
    </v-list>
  </v-sidebar>
</template>

<script>
export default {
  data() {
    return {
      defaultItemGroup: [
        { header: 'General' },
        { title: 'Home', href: '/', icon: 'home' },
        {
          title: 'Add Project',
          group: '/company',
          icon: 'library_add',
          items: [
            { form: true }
          ]
        },
        { divider: true },
        { header: 'Projects' }
      ],
      itemGroup: this.defaultItemGroup,
      newProjectName: '',
      addProjectGroupActive: false
    }
  },
  mounted() {
    this.itemGroup = this.defaultItemGroup
    axios.get('/api/projects')
    .then(({data}) => this.createItemGroup(data))
    .catch((error) => console.log(error))
  },
  methods: {
    createItemGroup( data ) {
      let group = this.defaultItemGroup
      for (let item of data) {
        group.push({
          title: item.name,
          href: '/project/' + item.slug,
          icon: 'timeline'
        })
      }
      this.itemGroup = group
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
