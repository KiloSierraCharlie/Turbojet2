/**
*
* Vuex documentation https://vuex.vuejs.org/
*/

import Vue from 'vue'
import Vuex from 'vuex'
import _ from 'lodash'
import Axios from 'axios'
import Router from 'src/Router.js'
import Config from 'src/Config.__ENV__.js'

Vue.use(Vuex)

/**
* States. https://vuex.vuejs.org/en/intro.html
* this is the local data of our application. Any components or class
* of the application can access these states in a read-only mode, but cannot write in these data. For that,
* we must use a mutation with a commit to ask a change of the data by the store
*
* Note: By default there is no data physical persistence
* Note 2: remember to set a default value to these states and to reset it in the resetAuthData mutation.
* otherwise the data will persist after a logout
*/
const state = {
    users: [],
    dynamicMenu: [],
    userDetails: {},
    groups: []
}


/**
* Getters to be used if we need to apply treatment to the state data before a component get it
*/
const getters = {}


/**
* Actions. https://vuex.vuejs.org/en/intro.html
* Mainly used for assync treatments (e.g: ajax call) or other business logic specific treatments
*/
const actions = {
    /**
    * Autenticte the user. Username is static = vendeur
    *
    * @param object Injection of the Store usefull function (dispatch, commit, state, etc.)
    * @param Object the credentials {login, password}
    * @return promise a promise object linked to the ajax call: Any component that dispatch
    * this action receive the promise object in response
    */
    // authUser({ dispatch, commit, state }, payload) {
    //     console.log("authUser", payload.username)
    //     console.log("authUser password", payload.password)
    //
    //     return Axios.post(Config.endpoint + 'login', payload)
    //         .then(function (response) {
    //             // Store the token
    //             commit('userAuthSuccess', response.data.token)
    //
    //             // Fetch user data
    //             // dispatch('fetchUserData') // TODO
    //
    //             Router.push({ name: 'page-news' })
    //
    //             return response
    //         })
    //         .catch(function (error) {
    //             throw error
    //         })
    //
    // },
    //

    userAuthSuccess({ dispatch, commit }, token) {
        commit('setToken', token)

        dispatch('fetchDynamicMenuSections')
    },

    /**
    * Logout the user : reset the user data and redirect him to the login page
    *
    * @param object Injection of the Store usefull function (dispatch, commit, state, etc.)
    * @return void
    */
    logoutUser({ commit }) {
        commit('resetAuthData')
        Router.push({ name: 'login' })
    },

    fetchDynamicMenuSections({ commit }) {
        Axios.get(Config.endpoint + 'menu')
            .then(function (response) {
                if(_.has(response.data, 'pages')) {
                    commit('setDynamicMenu', response.data.pages)
                }
            })
            .catch(function (error) {
                // if(_.has(error, 'message')) {
                //     $this.errorMessage = error.message
                //     $this.snackbar = true
                // }
                // else {
                //     $this.errorMessage = 'An error occured, please try again'
                //     $this.snackbar = true
                // }
            });
    },

    /**
    * Get the user list from the server
    *
    * @param object Injection of the Store usefull function (dispatch, commit, state, etc.)
    * @return promise a promise object linked to the ajax call: Any component that dispatch
    * this action receive the promise object in response
    */
    fetchUsersData({ commit }, groupId) {
        console.log('fetchUsersData')

        return Axios.get(Config.endpoint + 'users?includeGraduated=0&groupId='+groupId)
            .then(function (response) {
                commit('setUsers', response.data)
                console.log('fetchUsersData success', response.data)
            })
            .catch(function (error) {
                // TODO manage error
                console.log(error);
            });
    },

    fetchUserDetailsData({commit}, userId) {
        console.log('fetchUserDetailData')

        return Axios.get(Config.endpoint + 'users/'+userId)
            .then(function (response) {
                commit('setUserDetails', response.data)
                console.log('fetchUserDetailData success', response.data)
            })
            .catch(function (error) {
                // TODO manage error
                console.log(error);
            });
    },

    fetchGroupsData({ commit }) {
        console.log('fetchGroupsData')

        return Axios.get(Config.endpoint + 'groups?includeNonActive=0')
            .then(function (response) {
                commit('setGroups', response.data)
                console.log('fetchGroupsData success', response.data)
            })
            .catch(function (error) {
                // TODO manage error
                console.log(error);
            });
    },

    editUser({ commit }, payload) {
        return Axios.post(Config.endpoint + 'users/'+userId, payload)
            .then(function (response) {
                commit('setUserDetails', response.data)
                console.log('fetchUserDetailData success', response.data)

                return response
            })
            .catch(function (error) {
                throw error
            })
    },

    fetchNewsData({ commit }) {

    }
}


/**
* Mutations. https://vuex.vuejs.org/en/intro.html
* States musn't beeing modified directly by a component. A component must commit a
* mutation to ask the store to modify the data.
*/
const mutations = {
    setToken(state, token) {
        state.authToken = token

        // Store the token also in the local storage so it can be retreived later even if the app is
        // terminated
        window.localStorage.setItem('authToken', token)
        Axios.defaults.headers.common['AuthToken'] = token
    },

    setDynamicMenu(state, menu) {
        state.dynamicMenu = menu
    },

    resetAuthData(state) {
        state.authToken = null
        // state.userId = null

        // Reset the menu to it's initial state (from config file)
        state.menuConfig = _.cloneDeep(Config.navigation)

        // remove data from local storage
        window.localStorage.removeItem('authToken')
        Axios.defaults.headers.common['AuthToken'] = ''
    },

    setUsers(state, data) {
        console.log('setUsers', data)
        state.users = data
    },

    setUserDetails(state, data) {
        console.log('setUserDetails', data)
        state.userDetails = data
    },

    setGroups(state, data) {
        console.log('setGroups', data)
        state.groups = data
    }

}

export default new Vuex.Store({
    state,
    getters,
    actions,
    mutations,
    strict: true
})
