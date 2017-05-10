import VueRouter from 'vue-router';

let routes = [
    {
        path: '/',
        component: require('./views/Home')
    },
    {
        path: '/project/:project',
        component: require('./views/Project')
    }
]

export default new VueRouter({
    routes
});
