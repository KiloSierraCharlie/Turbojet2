<template>
    <v-container fluid grid-list-xl class="page-list-groups">
        <v-layout row wrap>
            <v-flex xs12 sm4 md3 lg3 xl2 v-for="group in groups" :key="group.id" @click="navigateGroupDetails(group.id)">
                <v-card class="group-card" height="350" >
                    <div style="background: url('http://api.turbojet.local/student_photos/cygnet.jpg') center center / cover no-repeat">
                        <v-card-media height="200px" :src="'http://api.turbojet.local/groups_photos/'+group.id+'.jpg'"/>
                    </div>

                    <v-card-title primary-title class="group-name">
                        <div class="d-block">
                            <span class="headline">{{group.name}}</span><br />
                            <span class="grey--text">{{group.type}}</span>
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
    name: 'page-list-groups',
    data() {
        return {
        }
    },
    computed: {
        groups() {
            return this.$store.state.groups
        },
    },
    created() {
        this.$store.dispatch('fetchGroupsData')
    },
    methods: {
        navigateGroupDetails(groupId) {
            this.$router.push({ name: 'page-group-details', params: { groupId: groupId }})
        }
    }
}
</script>

<style scoped lang="scss">
    .page-list-groups {
        .group-card {
            margin-bottom: 15px;
            text-align: center;
            cursor: pointer;

            .group-name > div{
                width: 100%;
            }

            .card-avatar {
                padding: 20px 0;
                height: 250px
            }
        }

    }
</style>
