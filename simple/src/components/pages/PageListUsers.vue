<template>
    <v-container fluid grid-list-xl class="page-list-users">
        <v-layout row wrap>
            <v-flex xs12 sm4 md3 lg3 xl2 v-for="user in users" :key="user.id" @click="navigateUserDetails(user.id)">
                <v-card class="user-card" height="350" >
                    <div style="background: url('http://api.turbojet.local/student_photos/cygnet.jpg') center center / cover no-repeat">
                        <v-card-media height="200px" :src="'http://api.turbojet.local/student_photos/'+user.id+'.jpg'"/>
                    </div>

                    <v-card-title primary-title class="user-name">
                        <div class="d-block">
                            <span class="headline">{{user.first_name}} {{user.last_name}}</span><br />
                            <span class="grey--text">{{user.title}}</span>
                        </div>
                    </v-card-title>
                </v-card>
            </v-flex>
        </v-layout>
    </v-container>
</template>

<script>
import moment from 'moment'
import _ from 'lodash'

export default {
    name: 'page-list-users',
    data() {
        return {
        }
    },
    props: ['groupId'],
    computed: {
        users() {
            return this.$store.state.users
        },
    },
    created() {
        console.log('created', this.$props)
        this.$store.dispatch('fetchUsersData', this.groupId)
    },
    methods: {
        navigateUserDetails(userId) {
            this.$router.push({ name: 'page-user-details', params: { userId: userId }})
        }
    }
}
</script>

<style scoped lang="scss">
    .page-list-users {
        .user-card {
            margin-bottom: 15px;
            text-align: center;
            cursor: pointer;

            .user-name > div{
                width: 100%;
            }

            .card-avatar {
                padding: 20px 0;
                height: 250px
            }
        }

    }
</style>
