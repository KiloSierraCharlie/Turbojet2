/**
* Bootstrap of the application. This module initialize the foundations of the applications.
* Any change in this module could lead to importants side-effects. Please consider every solutions
* before envisaging changing this logic
*/

import Vue from 'vue'
import Axios from 'axios'
import _ from 'lodash' // https://lodash.com/docs/
import moment from 'moment'
import App from './App.vue'
import Store from 'src/Store.js'
import Router from 'src/Router.js'
import Config from 'src/Config.__ENV__.js'
import Vuetify from 'vuetify'
Vue.use(Vuetify)
window.jQuery = window.$ = require('jquery')
import FullCalendar from 'vue-full-calendar'
import "fullcalendar-scheduler"
Vue.use(FullCalendar)
import VeeValidate from 'vee-validate'
Vue.use(VeeValidate)

/*
    Initialize the persistent data (token and preferred conference) from localstorage and pass it to the store
*/
console.info('Local storage --- initializing')
var token = window.localStorage.getItem('authToken')

if (token) {
    Store.commit('userAuthSuccess', token)
}

console.info('Local storage --- initialized')


/*
    Initializing Axios for ajax calls
    https://github.com/axios/axios
 */
// Vue.prototype.$http = Axios
console.info('Axios --- initializing')

// Axios.defaults.baseURL = 'https://api.example.com';
if (Config.apikey) {
    Axios.defaults.headers.common['ApiKey'] = Config.apikey
}
if (Store.state.authToken) {
    Axios.defaults.headers.common['AuthToken'] = Store.state.authToken
}

Axios.defaults.headers.post['Content-Type'] = 'application/json'
Axios.defaults.headers.common['Accept-Language'] = Config.lang

// Add a response interceptor
Axios.interceptors.response.use(
    function (response) {
        console.log('axios.interceptors response', response)

        return response;
    },
    function (error) {
        console.log('axios.interceptors error', error)
        // If any web service returns a 401, we logout the user and redirect him to the login page
        if (_.has(error, 'response.status') && error.response.status === 401 && Router.currentRoute.name !== 'login') {
            console.log('Interceptor: 401 not authorized')
            Store.commit('resetAuthData')
            Router.push({ name: 'login' })
        }
        return Promise.reject(error);
    }
);

console.info('Axios --- initialized')

/*
    Initializing Vue itself
    https://github.com/axios/axios
 */
console.info('Vue --- initializing')

new Vue({
    el: '#app',
    store: Store,
    components: { App },
    router: Router,
    template: "<App/>",
    created() {
        console.info('Vue --- initialized')
    }
})
