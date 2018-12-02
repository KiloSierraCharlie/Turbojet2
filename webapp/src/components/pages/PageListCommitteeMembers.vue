<template>
    <v-container fluid grid-list-lg class="page-list-committee-members pt-0">
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
            <v-flex sm3 v-for="(user, index) in userSelection" :key="user.id">
                <v-card class="user-card" @click.native="clickUser(user)">
                    <v-card-media
                        :src="endpoint+'media/student_photos/'+(user.picture ? user.picture : 'cygnet.jpg')"
                        height="200px"
                    ></v-card-media>

                    <v-card-title primary-title>
                        <div>
                            <h3 class="headline mb-0">{{user.first_name}} {{user.last_name}}</h3>
                            <div v-if="user.position">"{{user.position}}"</div>
                            <div class="chips">
                                <group-chip
                                    v-for="group in user.groups"
                                    :group-name="group.name"
                                    :group-type="group.type"
                                />
                            </div>
                        </div>
                    </v-card-title>
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
        <v-snackbar :timeout="0" color="red accent-2" v-model="snackbar">
          {{ errorMessage }}
          <v-btn dark flat @click.native="snackbar = false; errorMessage = ''">Close</v-btn>
        </v-snackbar>
    </v-container>
</template>

<script>
import moment from 'moment'
import Axios from 'axios'
import _ from 'lodash'
import Config from 'src/Config.__ENV__.js'
import GroupChip from 'components/GroupChip.vue'

export default {
    name: 'page-list-committee-members',
    data() {
        return {
            snackbar: false,
            errorMessage: '',
            usersData: [],
            filterGroup: ['1'],
            filterName: '',
            currentPage: 1,
            totalToDisplay: 25
        }
    },
    computed: {
        connectedUser() {
            return this.$store.state.connectedUser
        },
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
            const $this = this

            var mappedUsers = $this.usersData

            console.log('this.filterName', this.filterName)

            if(this.filterGroup.length > 0) {
                mappedUsers = _.filter($this.usersData, function(user) {
                    var groupsInCommon = _.intersection(
                        _.map(user.groups, function(group) {
                            return group.id
                        }),
                        $this.filterGroup
                    )

                    return groupsInCommon.length > 0
                })
            }
            if(this.filterName && this.filterName.length > 0) {
                mappedUsers = _.filter(mappedUsers.length > 1 ? mappedUsers : this.usersData, function(user) {
                    var name = _.lowerCase(user.first_name + user.last_name)
                    var filter = _.chain($this.filterName).trim().lowerCase().value()

                    return name.indexOf(filter) !== -1
                })
            }

            return _.sortBy(mappedUsers, 'first_name')
        },
        groupPicklist() {
            return _
                .chain(this.usersData)
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
        this.fetchData()
    },
    methods: {
        fetchData() {
            const $this = this

            Axios.get(Config.endpoint + 'users?includeGraduated=0')
                .then(function (response) {
                    $this.usersData = response.data
                    console.log('fetchData success', response.data)
                })
                .catch(function (error) {
                    $this.isLoading = false

                    console.log('error', error)

                    if(_.has(error, 'response.data.message')) {
                        $this.errorMessage = error.response.data.message
                        $this.snackbar = true
                    }
                    else {
                        $this.errorMessage = 'An error occured, please try again'
                        $this.snackbar = true
                    }
                });
        },
        clickUser(user) {
            this.$root.$emit('showUser', user.id)
        },
        navigateUserDetails(userId) {
            this.$router.push({ name: 'page-user-details', params: { userId: userId }})
        },
    },
    components: {'group-chip': GroupChip}
}
</script>

<style scoped lang="scss">
.page-list-committee-members {
    .chips {
        margin-top: 15px;
    }

    .user-card{
        cursor: pointer;
    }
}
</style>
