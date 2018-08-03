<template>
    <v-container fluid grid-list-lg class="page-list-users">
        <v-layout row>
            <v-flex xs12>
                <v-card class="pa-3">
                    <v-card-title primary-title class="pa-0 mb-0">
                        <h3 class="subheading">Filter</h3>
                    </v-card-title>
                    <v-layout row wrap>
                        <v-flex xs12 sm6>
                            <v-text-field
                                v-model="filterName"
                                label="Filter by name"
                                clearable
                            />
                        </v-flex>
                        <v-flex xs12 sm6>
                            <v-select
                                v-model="filterGroup" :items="groupPicklist"
                                item-text="name" item-value="name"
                                label="Filter by course or job" multiple
                                deletable-chips clearable
                            >
                                <template slot="selection" slot-scope="data">
                                    <group-chip
                                        :group-name="data.item.name"
                                        :group-type="data.item.type"
                                        close
                                        @remove="onRemoveGroupFromFilter(data.item)"
                                    />
                                </template>
                            </v-select>
                        </v-flex>
                    </v-layout>
                </v-card>
            </v-flex xs12>
        </v-layout row>
        <v-layout row class="mt-2 mb-2">
            <!-- <v-flex xs12 sm8 md10 class="pt-3">
                <div class="text-xs-center">
                    <v-pagination circle :length="totalPages" v-model="currentPage" :total-visible="7"></v-pagination>
                </div>
            </v-flex> -->
            <v-flex xs12 sm4 offset-sm8 offset-md10 md2>
                <v-select
                :items="[25, 50, 100]"
                v-model="totalToDisplay"
                label="Total to display"
                hide-details solo
                ></v-select>
            </v-flex>
        </v-layout>
        <v-layout row wrap>
            <v-flex d-flex v-for="(user, index) in userSelection" :key="user.id">
                <v-card>
                    <v-card-media
                        :src="endpoint+'media/student_photos/'+(user.picture ? user.picture : 'cygnet.jpg')"
                        height="200px"
                    ></v-card-media>

                    <v-card-title primary-title>
                        <div>
                            <h3 class="headline mb-0">{{user.first_name}} {{user.last_name}}</h3>
                            <div v-if="user.title">"{{user.title}}"</div>
                            <div class="chips">
                                <group-chip
                                    v-for="group in user.groups"
                                    :group-name="group.name"
                                    :group-type="group.type"
                                />
                            </div>
                        </div>
                    </v-card-title>
                    <!-- <v-card-actions>
                        <v-btn flat color="orange">Share</v-btn>
                        <v-btn flat color="orange">Explore</v-btn>
                    </v-card-actions> -->
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
import Config from 'src/Config.__ENV__.js'
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
        endpoint() {
            return Config.endpoint
        },
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

            // console.log('users filter', users)

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
        },
        onRemoveGroupFromFilter(item) {
            this.filterGroup.splice(this.filterGroup.indexOf(item.name), 1)
            this.filterGroup = [...this.filterGroup]
            console.log('removeGroupFromFilter', this.filterGroup, item)
        }
    },
    components: {'group-chip': GroupChip}
}
</script>

<style scoped lang="scss">
.page-list-users {
    .chips {
        margin-top: 15px;
    }
}
</style>
