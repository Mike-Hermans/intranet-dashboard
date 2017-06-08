<!DOCTYPE html>
<html lang="{{ config('app.locale') }}">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link href='https://fonts.googleapis.com/css?family=Roboto:300,400,500,700|Material+Icons' rel="stylesheet" type="text/css">
        <link rel="stylesheet" href="/css/app.css">
        <title>Dashboard</title>
        <script>
            window.csrfToken = '{{ csrf_token() }}';
        </script>
    </head>
    <body>
      <v-app id="app">
        <sidebar></sidebar>
        <toolbar></toolbar>
        <main>
          <v-container fluid>
            <router-view></router-view>
          </v-container>
        </main>
      </v-app>
      <script src="/js/app.js"></script>
    </body>
</html>
