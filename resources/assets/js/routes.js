import VueRouter from 'vue-router';

let routes = [
    {
        path: '/',
        component: require('./views/Home')
    },
    {
        path: '/network',
        component: require('./views/Network')
    },
    {
        path: '/project/:project',
        component: require('./views/Project')
    },
    {
        path: '/settings',
        component: require('./views/GeneralSettings')
    },
    {
        path: '/settings/:project',
        component: require('./views/ProjectSettings')
    },
    {
      path: '/add-project',
      component: require('./views/AddProject')
    }
]

export default new VueRouter({
    routes
});
