/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */
import './bootstrap'
import router from './routes'
import { EventBus } from './EventBus'

Vue.component('alert', require('./components/Alert'))
Vue.component('sidebar', require('./components/Sidebar'))
Vue.component('toolbar', require('./components/Toolbar'))
Vue.component('notification', require('./components/Notification'))

new Vue({
    el: '#app',
    router,
    data: {
        nav: true
    },
    created() {
      this.schedule = schedule.scheduleJob('10 * * * * *', function(){
        EventBus.$emit('global-update')
      });
    }
})
