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
    connectedUser: null,
    dynamicMenu: [],
    userDetails: {},
    groups: []
}


/**
* Getters to be used if we need to apply treatment to the state data before a component get it
*/
const getters = {

}


/**
* Actions. https://vuex.vuejs.org/en/intro.html
* Mainly used for assync treatments (e.g: ajax call) or other business logic specific treatments
*/
const actions = {

    userAuthSuccess({ dispatch, commit }, token) {
        commit('setToken', token)

        dispatch('fetchDynamicMenuSections')
        dispatch('fetchConnectedUserData')
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


    fetchConnectedUserData({commit, state}) {
        console.log('fetchConnectedUserData')

        return Axios.get(Config.endpoint + 'user')
            .then(function (response) {
                commit('setConnectedUser', response.data)
                console.log('fetchUserDetailData success', response.data)
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
    }
}


/**
* Mutations. https://vuex.vuejs.org/en/intro.html
* States musn't beeing modified directly by a component. A component must commit a
* mutation to ask the store to modify the data.
*/
const mutations = {
    setToken(state, token, userId) {
        state.authToken = token
        state.userId = userId

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

    setConnectedUser(state, data) {
        console.log('setConnectedUser', data)
        state.connectedUser = data


        // Compute permissions in one place
        state.connectedUser.permissions = []

        if(state.connectedUser.superAdmin) {
            state.connectedUser.permissions.push('superAdmin')
        }

        // user permissions
        _.each(data.userPermissions, function(value, key) {
            if(key.indexOf('permission_') === 0 && value === '1') {
                state.connectedUser.permissions.push(key)
            }
        })

        // group permissions
        _.each(data.groups, function(group) {
            _.each(group, function(value, key) {
                if(key.indexOf('permission_') === 0 && value === '1') {
                    state.connectedUser.permissions.push(key)
                }
            })
        })

        state.connectedUser.permissions = _.uniq(state.connectedUser.permissions)

        console.log('state.connectedUser.permissions', state.connectedUser.permissions)

        state.connectedUser.hasPermissions = function(name) {
            return this.permissions.indexOf(name) !== -1 || this.permissions.indexOf('superAdmin') !== -1
        }
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
