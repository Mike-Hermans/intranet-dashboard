/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */
import './bootstrap';
import router from './routes';

Vue.component('sidebar', require('./components/Sidebar'));
Vue.component('toolbar', require('./components/Toolbar'));

new Vue({
    el: '#app',
    router,
    data: {
        nav: null,
        toolbarItems: false
    }
});
