/**
* Bootstrap of the application. This module initialize the foundations of the applications.
* Any change in this module could lead to importants side-effects. Please consider every solutions
* before envisaging changing this logic
*/

import Vue from 'vue'
import VueI18n from 'vue-i18n'
import Axios from 'axios'
import _ from 'lodash' // https://lodash.com/docs/
import moment from 'moment'
import App from './App.vue'
import Store from 'src/Store.js'
import Router from 'src/Router.js'
import Config from 'src/Config.__ENV__.js'
import Vuetify from 'vuetify'

Vue.use(Vuetify)

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

// Add a request interceptor
// Axios.interceptors.request.use(
//     function (config) {
//         // Do something before request is sent
//         return config;
//     },
//     function (error) {
//         // Do something with request error
//         return Promise.reject(error);
//     }
// );
//
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
    Initialize i18n (assync)
    Documentation https://kazupon.github.io/vue-i18n/old/
*/
console.info('Vue i18n --- initializing')

// Make a request for a user with a given ID
Axios.get(Config.endpoint + 'translations/'+Config.lang)
    .then(function (response) {
        console.log('Get translations: ', response.data);

        Vue.use(VueI18n)

        const i18n = new VueI18n({
            locale: Config.lang,
            fallbackLocale: Config.lang,
            messages: response.data
        })

        // i18n.locale = Config.lang
        Axios.defaults.headers.common['Accept-Language'] = Config.lang
        document.querySelector('html').setAttribute('lang', Config.lang)
        // i18n.setLocaleMessage(Config.lang, response.data[Config.lang])

        console.info('Vue i18n --- initialized')
        initializeVue(i18n)
    })
    .catch(function (error) {
        // TODO manage error
        console.log(error);
    });

/*
    Method that will be called when all assync dependencies will be ready
*/
var initializeVue = function (i18n) {
    new Vue({
        el: '#app',
        i18n,
        store: Store,
        components: { App },
        router: Router,
        template: "<App/>",
        created() {
            // Initializing the menu from the config file
            // this.$store.commit('setMenuConfig', _.cloneDeep(Config.navigation))
        }
    })
}

// initializeVue()
