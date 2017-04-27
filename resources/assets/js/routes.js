import VueRouter from 'vue-router';

let routes = [
    {
        path: '/',
        component: require('./views/Home')
    },
    {
        path: '/project/:project',
        component: require('./views/Project'),
        children: [
          {
            path: '/',
            component: require('./views/project/Main')
          },
          {
            path: 'settings',
            component: require('./views/project/Settings')
          }
        ]
    }
]

export default new VueRouter({
    routes
});
