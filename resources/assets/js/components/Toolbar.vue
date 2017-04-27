<template>
    <v-toolbar>
      <v-toolbar-side-icon @click.native.stop="$parent.nav = !$parent.nav" class="hidden-md-and-up" />
      <v-toolbar-logo>Dashboard</v-toolbar-logo>
    </v-toolbar>
</template>

<script>
    export default {

    }
</script>
