<template>
    <v-container fluid grid-list-lg class="page-list-users pt-0">
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
                                item-text="text" item-value="value"
                                label="Filter by course or job" multiple
                                deletable-chips clearable
                            >
                                <template slot="selection" slot-scope="data">
                                    <group-chip
                                        :group-name="data.item.text"
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
        <v-layout row v-if="connectedUser ? connectedUser.hasPermissions('permission_user_filter_actions') : false">
            <v-flex xs12>
                <v-card class="pa-3">
                    <v-card-title primary-title class="pa-0 mb-0">
                        <h3 class="subheading">Actions</h3>
                    </v-card-title>
                    <v-layout row wrap>
                        <v-flex xs12>
                            <v-btn color="info" @click="onCopyEmails">Copy Email Adresses</v-btn>
                            <v-btn color="info" @click="onSendEmail">Send an Email</v-btn>
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
            <v-flex xs6 sm4 lg3 xl2 v-for="(user, index) in userSelection" :key="user.id">
                <v-card class="user-card" @click.native="clickUser(user)">
                    <v-card-media
                        :src="endpoint+'media/student_photos/'+(user.picture ? user.picture : 'cygnet.jpg')"
                        height="300px"
                    ></v-card-media>

                    <v-card-title primary-title>
                        <div>
                            <h3 class="headline mb-0">{{user.first_name}} {{user.last_name}}</h3>
                            <div v-if="user.position">{{user.position}}</div>
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
    name: 'page-list-users',
    data() {
        return {
            snackbar: false,
            errorMessage: '',
            usersData: [],
            groups: [],
            filterGroup: [],
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

            return mappedUsers
        },
        groupPicklist() {
            return this.groups;
        },
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

            Axios.get(Config.endpoint + 'picklists/groups')
                .then(function (response) {
                    $this.groups = response.data
                })
                .catch(this.displayError);
        },
        getEmailList() {
            return _.map(this.users, function(user) {
                return user.email
            })
        },
        onCopyEmails() {
            var emails = this.getEmailList()
            emails = emails.join(';') // outlook natively does not support standard comma separator

            const el = document.createElement('textarea')
            el.value = emails
            document.body.appendChild(el)
            el.select()
            document.execCommand('copy')
            document.body.removeChild(el)
        },
        onSendEmail() {
            var emails = this.getEmailList()
            var link = 'mailto:?bcc=' + emails
 
            window.location.href = link
        },
        clickUser(user) {
            this.$root.$emit('showUser', user.id)
        },
        navigateUserDetails(userId) {
            this.$router.push({ name: 'page-user-details', params: { userId: userId }})
        },
        onRemoveGroupFromFilter(item) {
            for( var i=0; i < this.filterGroup.length; i++ ){
                if( this.filterGroup[i] == item["value"] ){
                    this.filterGroup.splice( i, 1 );
                    return;
                }
            }
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

    .user-card{
        cursor: pointer;
    }
}
</style>
