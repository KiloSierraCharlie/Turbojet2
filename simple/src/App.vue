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
                            <v-list-tile avatar>
                                <v-list-tile-avatar>
                                    <img src="https://randomuser.me/api/portraits/men/85.jpg" >
                                </v-list-tile-avatar>
                                <v-list-tile-content>
                                    <v-list-tile-title>John Leider <v-icon small>mdi-menu-down</v-icon></v-list-tile-title>
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
                                <!-- <v-list-tile-content> -->
                                    <v-list-tile-title>
                                        {{ item.text }}
                                    </v-list-tile-title>
                                <!-- </v-list-tile-content> -->
                            </v-list-tile>
                            <v-list-tile v-for="(child, i) in item.children" :key="i" @click="clickMenu(child)" >
                                <v-list-tile-action v-if="child.icon">
                                    <v-icon>{{ child.icon }}</v-icon>
                                </v-list-tile-action>
                                <!-- <v-list-tile-content> -->
                                    <v-list-tile-title>
                                        {{ child.text }}
                                    </v-list-tile-title>
                                <!-- </v-list-tile-content> -->
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
            <v-toolbar v-if="$route.name !== 'login'" fixed app color="indigo" dark>
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
                <router-view></router-view>
                <!-- <router-view name="dialog"></router-view> -->
            </v-content>
            <v-footer color="indigo" class="pa-3 white--text" app>
                <!-- Made with love by&nbsp;<a target="_blank" href="https://www.linkedin.com/in/kevinbouhadana">Kevin Bouhadana (172)</a> -->
                ®2018
            </v-footer>
        </v-app>
    </div>
</template>

<script>
import _ from 'lodash'
import Axios from 'axios'
import Config from 'src/Config.__ENV__.js'

export default {
    data () {
        return {
            drawer: true,
            userMenuItems: [
                { icon: 'mdi-account', text: '(TODO prio 1) Profile', link: '/profile' },
                // { icon: 'mdi-settings', text: '(TODO prio 1) Settings', link: '/settings'  },
                { icon: 'mdi-logout-variant', text: 'Logout', action: this.logoutUser }
            ],
            drawerItems: [
                { icon: 'mdi-message-alert', text: 'News', link: '/news' },
                { icon: 'mdi-account-circle', text: 'Person Finder', link: '/users' },
                { icon: 'mdi-calendar', text: '(TODO prio 1) My Calendar' },
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
                        { icon: 'mdi-desktop-classic', text: 'CBT', link: 'http://ftecbt.com/login.html' }
                    ]
                },
                {
                    icon: 'mdi-airplane',
                    text: 'Flight Training',
                    children: [
                        { icon: 'mdi-cloud-download', text: 'Documents & Resources', link: '/documents/flying' },
                        { icon: 'mdi-timetable', text: 'Zeus', link: 'http://www.jmaero.com/zeus/' },
                        { icon: 'mdi-file-document', text: 'Allocation', link: '/documents/allocations' },
                        { icon: 'mdi-desktop-classic', text: 'CBT', link: 'http://ftecbt.com/login.html' }
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
                    text: '(TODO prio 2) Student Committee',
                    children: [
                        { icon: 'mdi-account-group', text: '(TODO) The Student Committee' },
                        { icon: 'mdi-cloud-download', text: 'Documents & Resources', link: '/documents/student-resources' }
                    ]
                },
                {
                    icon: 'mdi-briefcase',
                    'icon-alt': 'keyboard_arrow_down',
                    text: 'Career',
                    children: [
                        { icon: 'mdi-cloud-download', text: 'Documents & Resources', link: '/documents/career-resources' },
                        { icon: 'mdi-settings', text: 'Page Manager', link: '/page-manager/page-career' }
                    ],
                    customPagesPlaceholder: 'page-career'
                },
                {
                    icon: 'mdi-basketball',
                    text: 'Sport',
                    children: [
                        { icon: 'mdi-cloud-download', text: 'Documents & Resources', link: '/documents/sport-resources' },
                        { icon: 'mdi-settings', text: 'Page Manager', link: '/page-manager/page-sport' }
                    ],
                    customPagesPlaceholder: 'page-sport'
                },
                {
                    icon: 'mdi-silverware',
                    text: 'Entertainment',
                    children: [
                        { icon: 'mdi-cloud-download', text: 'Documents & Resources', link: '/documents/entertainment-resources' },
                        { icon: 'mdi-settings', text: 'Page Manager', link: '/page-manager/page-entertainment' }
                    ],
                    customPagesPlaceholder: 'page-entertainment'
                }
            ]
        }
    },
    computed: {
        // title() {
        //     var regex = /{(.*?)}/
        //     var tagValue = this.$route.meta.labels.title.match(regex)[1]
        //
        //     if(tagValue) {
        //         return this.$route.meta.labels.title.replace(regex, this.$route.params[tagValue])
        //     }
        //     else {
        //         return this.$route.meta.labels.title
        //     }
        // },
        displayTitle() {
            return _.has(this.$route, 'meta.labels.title')
        },
        mergedDrawerItems() {
            var mergedDrawerItems = _.cloneDeep(this.drawerItems)
            const $this = this

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
        }
    }
  }
</script>

<style lang="scss" src="scss/main.scss"></style>

<style lang="scss">
    #app {
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

        .footer {
            padding-left: 10px;
        }
    }
</style>
