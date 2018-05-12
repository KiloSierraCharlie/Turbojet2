<template>
    <div id="app">
        <v-app>
            <v-navigation-drawer v-if="$route.name !== 'login'" permanent clipped :mini-variant="mini" v-model="drawer" app>
                <v-list dense>
                    <template v-for="item in items">
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
                        <v-list-group v-else-if="item.children" v-model="item.model" :key="item.text" :prepend-icon="item.model ? item.icon : item['icon-alt']" append-icon="" >
                            <v-list-tile slot="activator">
                                <v-list-tile-content>
                                    <v-list-tile-title>
                                        {{ item.text }}
                                    </v-list-tile-title>
                                </v-list-tile-content>
                            </v-list-tile>
                            <v-list-tile v-for="(child, i) in item.children" :key="i" @click="" >
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
                        <v-list-tile v-else @click="" :key="item.text">
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
            <v-toolbar v-if="$route.name !== 'login'" clipped-left fixed app color="blue-grey" dark>
                <v-toolbar-side-icon @click.stop="mini = !mini"></v-toolbar-side-icon>
                <img src="/public/turbojet.svg" />
                <!-- <object data="/public/turbojet.svg" type="image/svg+xml"></object> -->
                <v-spacer></v-spacer>
                <v-toolbar-title v-text="$t('pagesTitle.page-list-users')"></v-toolbar-title>
                <v-spacer></v-spacer>
            </v-toolbar>
            <v-content>
                <!-- <v-slide-y-transition mode="out-in"> -->
                    <router-view></router-view>
                <!-- </v-slide-y-transition> -->
            </v-content>
            <v-footer color="blue-grey" class="white--text" app>
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
            mini: false,
            items: [
                { icon: 'announcement', text: 'News' },
                { icon: 'account_circle', text: 'Person Finder' },
                { icon: 'date_range', text: 'My calendar' },
                { icon: 'shopping_cart', text: 'FTEBay' },
                {
                    icon: 'keyboard_arrow_up',
                    'icon-alt': 'keyboard_arrow_down',
                    text: 'Facilities',
                    children: [
                        { icon: 'airport_shuttle', text: 'Minivan' },
                        { icon: 'tv', text: 'TV Room' },
                        { icon: 'build', text: 'IT / Maintenance request' }
                    ]
                },
                {
                    icon: 'keyboard_arrow_up',
                    'icon-alt': 'keyboard_arrow_down',
                    text: 'Groundschool',
                    children: [
                        { icon: 'cloud_download', text: 'Documents' },
                        { icon: 'computer', text: 'CBT' }
                    ]
                },
                {
                    icon: 'keyboard_arrow_up',
                    'icon-alt': 'keyboard_arrow_down',
                    text: 'Flight Training',
                    children: [
                        { icon: 'cloud_download', text: 'Documents & resources' },
                        { icon: 'computer', text: 'CBT' }
                    ]
                },
                {
                    icon: 'keyboard_arrow_up',
                    'icon-alt': 'keyboard_arrow_down',
                    text: 'Multi Pilot Training',
                    children: [
                        { icon: 'cloud_download', text: 'Documents & resources' },
                        { icon: 'computer', text: 'CBT' }
                    ]
                },
                {
                    icon: 'keyboard_arrow_up',
                    'icon-alt': 'keyboard_arrow_down',
                    text: 'Career',
                    children: [
                        { icon: 'announcement', text: 'Note from the Career Rep' },
                        { icon: 'cloud_download', text: 'Documents & resources' }
                    ]
                },
                {
                    icon: 'keyboard_arrow_up',
                    'icon-alt': 'keyboard_arrow_down',
                    text: 'Sport',
                    children: [
                        { icon: 'announcement', text: 'Advice section' },
                        { icon: 'chat', text: 'Groups' },
                        { icon: 'date_range', text: 'Events' },
                        { icon: 'thumb_up', text: 'Suggestions' },
                    ]
                }
            ],
            miniVariant: false
        }
    }
  }
</script>

<style lang="scss" src="scss/main.scss"></style>

<style lang="scss">
    #app {
        .toolbar {
            img {
                height: 35px;
            }
        }

        .footer {
            padding-left: 10px;
        }
    }
</style>
