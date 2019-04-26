<template>
    <div id="app">
        <v-app>
            <v-navigation-drawer
                v-if="$route.name !== 'login'" v-model="drawer"
                class="grey darken-4" dark
                app fixed>
                <v-toolbar flat class="elevation-1" color="transparent">
                    <v-menu offset-x>
                        <v-list slot="activator" class="pa-0">
                            <v-list-tile avatar v-if="connectedUser">
                                <v-list-tile-avatar>
                                    <div class="background" :style="'background-image: url(' + endpoint + 'media/student_photos/'+ (connectedUser.picture ? connectedUser.picture : 'cygnet.jpg') + ')'"></div>
                                </v-list-tile-avatar>
                                <v-list-tile-content>
                                    <v-list-tile-title>{{connectedUser.firstName}}<v-icon small>mdi-menu-down</v-icon></v-list-tile-title>
                                </v-list-tile-content>
                            </v-list-tile>
                        </v-list>
                        <v-list>
                            <v-list-tile v-for="(item, index) in userMenuItems" :key="index" @click="clickMenu(item)">
                                <v-list-tile-action><v-icon>{{ item.icon }}</v-icon></v-list-tile-action>
                                <v-list-tile-title>{{ item.text }}</v-list-tile-title>
                            </v-list-tile>
                        </v-list>
                    </v-menu>
                </v-toolbar>
                <v-list dense>
                    <template v-for="item in mergedDrawerItems">
                        <v-list-group v-if="item.children" v-model="item.model" :key="item.text" :prepend-icon="item.icon">
                            <v-list-tile slot="activator">
                                    <v-list-tile-title>
                                        {{ item.text }}
                                    </v-list-tile-title>
                            </v-list-tile>
                            <v-list-tile v-for="(child, i) in item.children" :key="i" @click="clickMenu(child)" >
                                <v-list-tile-action v-if="child.icon">
                                    <v-icon>{{ child.icon }}</v-icon>
                                </v-list-tile-action>
                                <v-list-tile-title>
                                    {{ child.text }}
                                </v-list-tile-title>
                            </v-list-tile>
                        </v-list-group>
                        <v-list-tile v-else :key="item.text" @click="clickMenu(item)">
                            <v-list-tile-action>
                                <v-icon>{{ item.icon }}</v-icon>
                            </v-list-tile-action>
                            <v-list-tile-content>
                                <v-list-tile-title>
                                        {{ item.text }}
                                </v-list-tile-title>
                            </v-list-tile-content>
                        </v-list-tile>
                    </template>
                </v-list>
            </v-navigation-drawer>
            <v-toolbar v-if="$route.name !== 'login'" fixed app color="primary" dark>
                <v-toolbar-side-icon @click.stop="drawer = !drawer"></v-toolbar-side-icon>
                <img class="logo ml-3" src="/public/turbojet.svg" />
            </v-toolbar>
            <v-content>
                <v-container fluid v-if="$route.name !== 'login' && displayTitle">
                    <v-layout row>
                        <v-flex xs12>
                            <v-card class="headline pa-3">{{$route.meta.labels.title}}</v-card>
                        </v-flex>
                    </v-layout>
                </v-container>
                <router-view ref="view" :class="{'mb-5': $route.name !== 'page-it-reps'}"></router-view>
                <!-- <router-view name="dialog"></router-view> -->
            </v-content>
            <v-footer v-if="$route.name !== 'page-it-reps'" color="primary" class="hidden-xs-only px-2 white--text" height="auto" app>
                <v-layout justify-center row wrap>
                    <v-flex class="pt-2 pb-2" text-xs-center white--text xs12>
                        Turbojet V{{version}} made with &nbsp;<v-icon class="subheading" color="white">mdi-heart</v-icon>&nbsp; and maintained by a <router-link to="/it-reps">lineage of wonderful IT Reps</router-link>. Current IT Rep <router-link to="/committee">Francisco Jesús Jiménez Hidalgo (180)</router-link>
                    </v-flex>
                </v-layout>
            </v-footer>
            <user-details-dialog ref="userDetailsDialog"
                :user-data="editedUser"
                :groups-data="groups"
                :show="dialogEdit"
                :is-same-user="editedUser ? editedUser.id === connectedUser.id : false"
                :has-permissions="connectedUser ? connectedUser.hasPermissions('permission_edit_user') : false"
                @saveUser="onSaveUser"
                @verifyUser="onVerifyUser"
                @banUser="onBanUser"
                @unbanUser="onUnbanUser"
                @addUserToMinivan="onAddUserToMinivan"
                @removeUserFromMinivan="onRemoveUserFromMinivan"
                @closeDialogEdit="onCloseDialogEdit"
                @deleteUser="dialogDelete = true;"
                :loading="isLoading"/>
            <v-snackbar :timeout="0" color="red accent-2" v-model="snackbar">
              {{ errorMessage }}
              <v-btn dark flat @click.native="snackbar = false; errorMessage = ''">Close</v-btn>
            </v-snackbar>
        </v-app>
    </div>
</template>

<script>
import _ from 'lodash'
import Axios from 'axios'
import Config from 'src/Config.__ENV__.js'
import UserDetailDialog from 'components/UserDetailDialog.vue'

export default {
    data () {
        return {
            dialogEdit: false,
            dialogDelete: false,
            editedUser: null,
            groups: null,
            isLoading: false,
            snackbar: false,
            errorMessage: '',
            drawer: true,
            userMenuItems: [
                { icon: 'mdi-account', text: 'My Profile', action: this.openUserProfile },
                // { icon: 'mdi-settings', text: '(TODO prio 1) Settings', link: '/settings'  },
                { icon: 'mdi-logout-variant', text: 'Logout', action: this.logoutUser }
            ],
            drawerItems: [
                {
                    icon: 'mdi-settings',
                    text: 'Admin & Course Leaders',
                    permission: 'permission_approve_user',
                    children: [
                        { icon: 'mdi-account-group', text: 'User Verification' , link: '/admin/verify'}
                    ]
                },
                { icon: 'mdi-message-alert', text: 'News', link: '/news' },
                { icon: 'mdi-account-circle', text: 'Person Finder', link: '/users' },
                { icon: 'mdi-calendar', text: 'My Zeus Calendar', link: '/my-zeus-calendar' },
                { icon: 'mdi-cart', text: 'FTEBay', link: '/ftebay'  },
                {
                    icon: 'mdi-domain',
                    text: 'Administration & Facilities',
                    children: [
                        { icon: 'mdi-cloud-download', text: 'Documents & Resources', link: '/documents/administration'},
                        { icon: 'mdi-van-passenger', text: 'Minivan Booking', link: '/bookings/minivan'},
                        { icon: 'mdi-television-classic', text: 'TV Room Booking', link: '/bookings/tv'},
                        { icon: 'mdi-fire', text: 'Barbecue Booking', link: '/bookings/barbecue'},
                        { icon: 'mdi-wrench', text: 'IT / Maintenance Request', link: 'http://request.ftejerez.com' }
                    ]
                },
                {
                    icon: 'mdi-projector-screen',
                    text: 'Groundschool',
                    children: [
                        { icon: 'mdi-cloud-download', text: 'Documents & Resources', link: '/documents/ground-school' },
                        { icon: 'mdi-desktop-classic', text: 'CBT', link: 'http://ftecbt.com/login.html' },
                        { icon: 'mdi-desktop-classic', text: 'OLD CBT', link: 'http://intranet/cbt/old/index.php' }
                    ]
                },
                {
                    icon: 'mdi-airplane',
                    text: 'Flight Training',
                    children: [
                        { icon: 'mdi-cloud-download', text: 'Documents & Resources', link: '/documents/flying' },
                        { icon: 'mdi-timetable', text: 'Zeus (Mobile)', link: 'http://www.jmaero.com/zeus/' },
                        { icon: 'mdi-timetable', text: 'Zeus (PC)', link: 'http://zeus/' },
                        { icon: 'mdi-file-document', text: 'Allocation', link: '/documents/allocations' },
                        { icon: 'mdi-desktop-classic', text: 'CBT', link: 'http://ftecbt.com/login.html' },
                        { icon: 'mdi-desktop-classic', text: 'OLD CBT', link: 'http://intranet/cbt/old/index.php' }
                    ]
                },
                {
                    icon: 'mdi-account-multiple',
                    text: 'Multi Pilot Training',
                    children: [
                        { icon: 'mdi-cloud-download', text: 'Documents & Resources', link: '/documents/multi-pilot-resources' },
                        { icon: 'mdi-calendar', text: '737 Sim TimeTable', link: '/documents/737-sim-timetable' }
                    ]
                },
                {
                    icon: 'mdi-star-circle',
                    text: 'The Student Committee',
                    children: [
                        { icon: 'mdi-account-group', text: 'The members' , link: '/committee'},
                        { icon: 'mdi-cloud-download', text: 'Documents & Resources', link: '/documents/student-resources' }
                    ]
                },
                {
                    icon: 'mdi-briefcase',
                    'icon-alt': 'keyboard_arrow_down',
                    text: 'Career',
                    children: [
                        { icon: 'mdi-cloud-download', text: 'Documents & Resources', link: '/documents/career-resources' },
                        { icon: 'mdi-settings', text: 'Page Manager', link: '/page-manager/page-career', permission: 'permission_careers_rep' }
                    ],
                    customPagesPlaceholder: 'page-career'
                },
                {
                    icon: 'mdi-basketball',
                    text: 'Sport',
                    children: [
                        { icon: 'mdi-cloud-download', text: 'Documents & Resources', link: '/documents/sport-resources' },
                        { icon: 'mdi-settings', text: 'Page Manager', link: '/page-manager/page-sport', permission: 'permission_sports_rep' }
                    ],
                    customPagesPlaceholder: 'page-sport'
                },
                {
                    icon: 'mdi-silverware',
                    text: 'Entertainment',
                    children: [
                        { icon: 'mdi-cloud-download', text: 'Documents & Resources', link: '/documents/entertainment-resources' },
                        { icon: 'mdi-settings', text: 'Page Manager', link: '/page-manager/page-entertainment', permission: 'permission_entertainment_rep'}
                    ],
                    customPagesPlaceholder: 'page-entertainment'
                }
            ]
        }
    },
    computed: {
        _() {
            return _
        },
        endpoint() {
            return Config.endpoint
        },
        version() {
            return Config.version
        },
        connectedUser() {
            return this.$store.state.connectedUser
        },
        displayTitle() {
            return _.has(this.$route, 'meta.labels.title')
        },
        mergedDrawerItems() {
            var mergedDrawerItems = _.cloneDeep(this.drawerItems)
            const $this = this

            // Hide menu items which necessitate special permission (e.g: reps)
            // if the user does not have those permissions
            _.remove(mergedDrawerItems, function(item) {
                if(_.has(item, 'permission') && $this.connectedUser && !$this.connectedUser.hasPermissions(item.permission)) {
                    return true
                }
                else if(!$this.connectedUser) {
                    return true
                }
                else {
                    return false
                }
            })

            _.each(mergedDrawerItems, function(item) {
                if(_.has(item, 'children')) {
                    _.remove(item.children, function(child) {
                        if(_.has(child, 'permission') && $this.connectedUser && !$this.connectedUser.hasPermissions(child.permission)) {
                            return true
                        }
                        else if(!$this.connectedUser) {
                            return true
                        }
                        else {
                            return false
                        }
                    })
                }
            })

            // Load dynamically loaded page (i.e pages created in base by Student committee Reps)
            _.each(this.$store.state.dynamicMenu, function(page) {
                var menuIndex = _.findIndex(mergedDrawerItems, { 'customPagesPlaceholder': page.type})

                // Adding the page to the corresponding menu section (e.g.: page-sport)
                if(menuIndex !== -1) {
                    if(!_.has($this.drawerItems[menuIndex], 'children')) {
                        $this.drawerItems[menuIndex].children = []
                    }

                    mergedDrawerItems[menuIndex].children.push({ icon: 'mdi-file-document-box', text: page.title, link: '/pages/'+page.type+'/'+page.id })
                }
            })

            return mergedDrawerItems
        }
    },
    methods: {
        clickMenu(item) {
            if (_.has(item, 'link')) {
                if(item.link.indexOf('http') == 0) {
                    window.open(item.link)
                }
                else {
                    this.$router.push({ path: item.link })
                }
            }
            else if (_.has(item, 'action')) {
                item.action()
            }
        },
        logoutUser() {
            this.$store.dispatch('logoutUser')
        },
        openUserProfile() {
            this.$root.$emit('showUser', this.connectedUser.id)
        },
        openUserDialog(userId) {
            console.log('openUserDialog', userId)

            const $this = this
            Axios.get(Config.endpoint + 'users/' + userId)
                .then(function(response) {
                    $this.editedUser = response.data

                    $this.editedUser.notification_news = $this.editedUser.notification_news === '1' ? true : false
                    $this.editedUser.notification_ftebay = $this.editedUser.notification_ftebay === '1' ? true : false
                    $this.editedUser.notification_zeus = $this.editedUser.notification_zeus === '1' ? true : false

                    if(_.has($this.editedUser, 'groups') && !_.isEmpty($this.editedUser.groups)) {
                        $this.editedUser.groups = _.map($this.editedUser.groups, function(group) {
                            return group.id
                        })
                    }

                    console.log('$this.editedUser', $this.editedUser)
                })
                .catch(this.displayError)

            Axios.get(Config.endpoint + 'picklists/groups')
                .then(function (response) {
                    $this.groups = response.data
                })
                .catch(this.displayError);

            this.dialogEdit = true
        },

        onCloseDialogEdit() {
            const $this = this

            this.dialogEdit = false
            this.snackbar = false

            setTimeout(() => {
                $this.resetForm()
            }, 300)
        },
        onVerifyUser() {
            const $this = this

            Axios.post(Config.endpoint + 'verify-users/verify/' + this.editedUser.id)
                .then(function(response) {
                    $this.isLoading = false
                    $this.onCloseDialogEdit()
                })
                .catch(this.displayError)

            this.isLoading = true
        },
        onBanUser() {
            const $this = this

            Axios.post(Config.endpoint + 'verify-users/ban/' + this.editedUser.id)
                .then(function(response) {
                    $this.isLoading = false
                    $this.onCloseDialogEdit()
                })
                .catch(this.displayError)

            this.isLoading = true
        },
        onUnbanUser() {
            const $this = this

            Axios.post(Config.endpoint + 'verify-users/unban/' + this.editedUser.id)
                .then(function(response) {
                    $this.isLoading = false
                    $this.onCloseDialogEdit()
                })
                .catch(this.displayError)

            this.isLoading = true
        },
        onAddUserToMinivan() {
            const $this = this

            Axios.post(Config.endpoint + 'verify-users/minivan/add/' + this.editedUser.id)
                .then(function(response) {
                    $this.isLoading = false
                    $this.onCloseDialogEdit()
                })
                .catch(this.displayError)

            this.isLoading = true
        },
        onRemoveUserFromMinivan() {
            const $this = this

            Axios.post(Config.endpoint + 'verify-users/minivan/remove/' + this.editedUser.id)
                .then(function(response) {
                    $this.isLoading = false
                    $this.onCloseDialogEdit()
                })
                .catch(this.displayError)

            this.isLoading = true
        },
        onSaveUser(userData) {
            const $this = this

            var payload = {}

            _.each(userData, function(value, key) {
                if(value !== $this.editedUser[key] && key !== 'confirmPassword') {
                    payload[key] = value
                }
            })

            console.log('payload', payload)

            if(!_.isEmpty(payload)) {
                Axios.post(Config.endpoint + 'users/' + this.editedUser.id, payload)
                    .then(function(response) {
                        $this.isLoading = false
                        $this.onCloseDialogEdit()

                        if($this.$route.name === 'page-list-users' || $this.$route.name === 'page-my-zeus-calendar') {
                            $this.$refs.view.fetchData()
                        }
                    })
                    .catch(this.displayError)

                this.isLoading = true
            }
        },
        deleteUser() {
            const $this = this

            // TODO delete or ban user

            // Axios.post(Config.endpoint + this.$route.meta.api.changeState.replace('{id}', this.editedEvent.data.id), {cancelled: true})
            //     .then(function(response) {
            //         $this.isLoading = false
            //         $this.onCloseDialogEdit()
            //         $this.dialogCancel = false
            //         $this.$refs.calendar.fireMethod('refetchEvents')
            //     })
            //     .catch(this.displayError)
        },
        resetForm() {
            this.$refs.userDetailsDialog.resetForm()
            this.editedUser = null
        },

        displayError(error) {
            this.isLoading = false

            console.log('error', error)

            if(_.has(error, 'response.data.message')) {
                this.errorMessage = error.response.data.message
                this.snackbar = true
            }
            else {
                this.errorMessage = 'An error occured, please try again'
                this.snackbar = true
            }
        }
    },
    created() {
        this.$root.$on('showUser', this.openUserDialog)
    },
    components: {
        'user-details-dialog': UserDetailDialog
    }
}
</script>

<style lang="scss" src="scss/main.scss"></style>

<style lang="scss">
    #app {
        background: #f6f6f6;

        .v-menu {
            .v-avatar {
                overflow: hidden;

                .background {
                    background-position: center;
                    background-repeat: no-repeat;
                    background-size: cover;
                    width: 100%;
                    height: 100%;
                }
            }
        }

        .v-toolbar {
            img.logo {
                height: 30px;
            }
        }

        .navigation-drawer {
            .list__group__items {
                background-color: #2d2d2d;
            }
        }

        .v-footer {
            // padding-left: 10px;

            a, a:visited {
                color: white;

            }
        }
    }
</style>
