
import Vue from 'vue'
import axios from 'axios'
import moment from 'moment'
import VueRouter from 'vue-router'
import Vuetify from 'vuetify'
import VueHighcharts from 'vue-highcharts'
import Highcharts from 'highcharts/highstock'
import highchartsMore from 'highcharts/highcharts-more'

highchartsMore(Highcharts)
window.Vue = Vue
Vue.use(VueRouter)
Vue.use(Vuetify)
Highcharts.setOptions({
  global: {
    useUTC: false
  }
})
Vue.use(VueHighcharts, { Highcharts })
Vue.use(require('vue-moment'), { moment })
window.axios = axios
window.schedule = require('node-schedule')

window.axios.defaults.headers.common['X-CSRF-TOKEN'] = window.csrfToken
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest'
