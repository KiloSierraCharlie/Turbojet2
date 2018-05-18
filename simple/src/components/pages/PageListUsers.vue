<template>
    <v-container fluid class="page-list-users">
        <v-layout row>
            <v-flex xs12>
                <v-card class="pa-3">
                    Search

                    <v-text-field
                        v-model="filterName"
                        label="Filter by name"
                        clearable
                    />
                    <v-select
                        v-model="filterGroup" :items="groupPicklist"
                        item-text="name" item-value="name"
                        label="Filter by course or job" multiple chips
                    >
                        <template slot="selection" slot-scope="data">
                            <group-chip :group-name="data.item.name" :group-type="data.item.type" />
                        </template>
                    </v-select>
                </v-card>
            </v-flex xs12>
        </v-layout row>
        <v-layout row>
            <v-flex xs12>
                <v-select
                    :items="[25, 50, 100]"
                    v-model="totalToDisplay"
                    label="Total to display"
                    single-line
                ></v-select>
            </v-flex>
        </v-layout>

        <v-layout row>
            <v-flex xs12>
                <v-card>
                    <v-list three-line>
                        <template v-for="(user, index) in userSelection">
                            <v-list-tile avatar :key="'user-'+user.id" @click="navigateUserDetails(user.id)">
                                <v-list-tile-avatar size="80">
                                    <img :src="user.picture ? 'http://api.turbojet.local/student_photos/'+user.picture : 'http://api.turbojet.local/student_photos/cygnet.jpg'">
                                </v-list-tile-avatar>
                                <v-list-tile-content>
                                    <v-list-tile-title>{{user.first_name}} {{user.last_name}}</v-list-tile-title>
                                    <v-list-tile-sub-title>{{user.title}}</v-list-tile-sub-title>
                                    <div>
                                        <group-chip v-for="group in user.groups" :group-name="group.name" :group-type="group.type" />
                                    </div>
                                </v-list-tile-content>
                            </v-list-tile>
                            <v-divider v-if="index + 1 < users.length" :key="'divider-'+index"></v-divider>
                        </template>
                    </v-list>
                </v-card>
            </v-flex>
        </v-layout>
        <v-layout row class="mt-3">
            <v-flex xs12>
                <div class="text-xs-center">
                    <v-pagination circle :length="totalPages" v-model="currentPage" :total-visible="7"></v-pagination>
                </div>
            </v-flex>
        </v-layout>
    </v-container>
</template>

<script>
import moment from 'moment'
import _ from 'lodash'
import GroupChip from 'components/GroupChip.vue'

export default {
    name: 'page-list-users',
    data() {
        return {
            filterGroup: [],
            filterName: '',
            currentPage: 1,
            totalToDisplay: 25
        }
    },
    computed: {
        totalPages() {
            return _.ceil(this.users.length / this.totalToDisplay)
        },
        userSelection() {
            return _.slice(this.users, (this.currentPage-1)*this.totalToDisplay, (this.currentPage-1)*this.totalToDisplay+this.totalToDisplay)
        },
        users() {
            var users = this.$store.state.users
            var that = this

            if(this.filterGroup.length > 0) {
                users = _.filter(users, function(user) {
                    var groupsInCommon = _.intersection(
                        _.map(user.groups, function(group) {
                            return group.name
                        }),
                        that.filterGroup
                    )

                    return groupsInCommon.length > 0
                })
            }
            if(this.filterName.length > 0) {
                users = _.filter(users, function(user) {
                    var name = _.lowerCase(user.first_name + user.last_Name)
                    var filter = _.chain(that.filterName).trim().lowerCase().value()

                    return name.indexOf(filter) !== -1
                })
            }

            return _.sortBy(users, 'first_name')
        },
        groupPicklist() {
            return _
                .chain(this.$store.state.users)
                .map(function(user) {
                    return user.groups
                })
                .flatten()
                .uniqBy('name')
                .sortBy('name')
                .value()
            ;
        }
    },
    created() {
        this.$store.dispatch('fetchUsersData')
    },
    methods: {
        navigateUserDetails(userId) {
            this.$router.push({ name: 'page-user-details', params: { userId: userId }})
        }
    },
    components: {'group-chip': GroupChip}
}
</script>

<style scoped lang="scss">
    .page-list-users {
        .list--three-line .list__tile__content {
            padding-left: 20px;
        }

        .list--three-line .list__tile__avatar {
            margin-top: 0;
        }

        /* TODO */
        .list--three-line .list__tile--avatar {
            height: 100px;
        }

    }
</style>
