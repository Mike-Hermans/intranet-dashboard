
import Vue from 'vue'
import axios from 'axios'
import moment from 'moment'
import VueRouter from 'vue-router'
import Vuetify from 'vuetify'
import VueHighcharts from 'vue-highcharts'
import Highcharts from 'highcharts/highstock'

window.Vue = Vue
Vue.use(VueRouter)
Vue.use(Vuetify)
Vue.use(VueHighcharts, { Highcharts })
Vue.use(require('vue-moment'), { moment })
window.axios = axios

window.axios.defaults.headers.common['X-CSRF-TOKEN'] = window.csrfToken
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest'

/**
 * Echo exposes an expressive API for subscribing to channels and listening
 * for events that are broadcast by Laravel. Echo and event broadcasting
 * allows your team to easily build robust real-time web applications.
 */

// import Echo from 'laravel-echo'

// window.Pusher = require('pusher-js');

// window.Echo = new Echo({
//     broadcaster: 'pusher',
//     key: 'your-pusher-key'
// });
