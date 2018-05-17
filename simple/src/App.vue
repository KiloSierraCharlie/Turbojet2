<template>
    <div id="app">
        <v-app>
            <v-navigation-drawer
                v-if="$route.name !== 'login'" v-model="drawer"
                class="grey darken-4" dark
                app permanent>
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
                    <template v-for="item in drawerItems">
                        <v-layout row v-if="item.heading" align-center :key="item.heading">
                            <v-flex xs6>
                                <v-subheader v-if="item.heading">
                                    {{ item.heading }}
                                </v-subheader>
                            </v-flex>
                            <v-flex xs6 class="text-xs-center">
                                <a href="#!" class="body-2 black--text">EDIT</a>
                            </v-flex>
                        </v-layout>
                        <v-list-group v-else-if="item.children" v-model="item.model" :key="item.text" :prepend-icon="item.icon">
                            <v-list-tile slot="activator">
                                <v-list-tile-content>
                                    <v-list-tile-title>
                                        {{ item.text }}
                                    </v-list-tile-title>
                                </v-list-tile-content>
                            </v-list-tile>
                            <v-list-tile v-for="(child, i) in item.children" :key="i" @click="clickMenu(child)" >
                                <v-list-tile-action v-if="child.icon">
                                    <v-icon>{{ child.icon }}</v-icon>
                                </v-list-tile-action>
                                <v-list-tile-content>
                                    <v-list-tile-title>
                                        {{ child.text }}
                                    </v-list-tile-title>
                                </v-list-tile-content>
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
                <img src="/public/turbojet.svg" />
                <!-- <object data="/public/turbojet.svg" type="image/svg+xml"></object> -->
                <v-spacer></v-spacer>
                <!-- <v-toolbar-title v-text="$t('pagesTitle.page-list-users')"></v-toolbar-title> -->
                <v-spacer></v-spacer>
            </v-toolbar>
            <v-content>
                <v-container fluid v-if="$route.name !== 'login'">
                    <v-layout row>
                        <v-flex xs12>
                            <v-card class="headline pa-3">{{$route.meta.title}}</v-card>
                        </v-flex>
                    </v-layout>
                </v-container>
                <router-view></router-view>
            </v-content>
            <v-footer color="indigo" class="pa-3 white--text" app>
                <span v-html="$t('footer.copyright')"/>
            </v-footer>
        </v-app>
    </div>
</template>

<script>
  export default {
    data () {
        return {
            drawer: false,
            userMenuItems: [
                { icon: 'mdi-account', text: 'Profile', link: 'profile' },
                { icon: 'mdi-settings', text: 'Settings', link: 'settings'  },
                { icon: 'mdi-logout-variant', text: 'Logout', action: this.logoutUser }
            ],
            drawerItems: [
                { icon: 'mdi-message-alert', text: 'News', link: 'page-news' },
                { icon: 'mdi-account-circle', text: 'Person Finder', link: 'page-list-users' },
                { icon: 'mdi-calendar', text: 'My calendar' },
                { icon: 'mdi-cart', text: 'FTEBay' },
                {
                    icon: 'mdi-domain',
                    text: 'Facilities',
                    children: [
                        { icon: 'mdi-van-passenger', text: 'Minivan' },
                        { icon: 'mdi-television-classic', text: 'TV Room' },
                        { icon: 'mdi-wrench', text: 'IT / Maintenance request', link: 'http://intranet/request/' }
                    ]
                },
                {
                    icon: 'mdi-projector-screen',
                    text: 'Groundschool',
                    children: [
                        { icon: 'mdi-cloud-download', text: 'Documents' },
                        { icon: 'mdi-desktop-classic', text: 'CBT', link: 'http://ftecbt.com/login.html' }
                    ]
                },
                {
                    icon: 'mdi-airplane',
                    text: 'Flight Training',
                    children: [
                        { icon: 'mdi-cloud-download', text: 'Documents' },
                        { icon: 'mdi-desktop-classic', text: 'CBT', link: 'http://ftecbt.com/login.html' }
                    ]
                },
                {
                    icon: 'mdi-account-multiple',
                    text: 'Multi Pilot Training',
                    children: [
                        { icon: 'mdi-cloud-download', text: 'Documents' },
                        { icon: 'mdi-calendar', text: '737 Sim TimeTable' }
                    ]
                },
                {
                    icon: 'mdi-briefcase',
                    'icon-alt': 'keyboard_arrow_down',
                    text: 'Career',
                    children: [
                        { icon: 'mdi-message-alert', text: 'Note from the Career Rep' },
                        { icon: 'mdi-cloud-download', text: 'Documents & Resources' },
                    ]
                },
                {
                    icon: 'mdi-basketball',
                    text: 'Sport',
                    children: [
                        { icon: 'mdi-message-alert', text: 'Advice Section' },
                        { icon: 'mdi-message-text', text: 'Groups' },
                        { icon: 'mdi-calendar', text: 'Events' },
                        { icon: 'mdi-lightbulb-on', text: 'Suggestions' },
                    ]
                },
                { icon: 'mdi-silverware', text: 'FTE Discounts' },
            ]
        }
    },
    methods: {
        clickMenu(item) {
            if (_.has(item, 'link')) {
                if(item.link.indexOf('http') == 0) {
                    window.open(item.link)
                }
                else {
                    this.$router.push({ name: item.link })
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
        .toolbar {
            img {
                height: 30px;
            }
        }

        .footer {
            padding-left: 10px;
        }
    }
</style>
