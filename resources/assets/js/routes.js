import VueRouter from 'vue-router';

let routes = [
    {
        path: '/',
        component: require('./views/Home')
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
    },
    {
      path: '/test/home-1',
      component: require('./test/Home1')
    },
    {
      path: '/test/home-2',
      component: require('./test/Home2')
    }
]

export default new VueRouter({
    routes
});
