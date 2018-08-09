<template>
    <v-container fluid class="page-user-details">
        <v-form>
            <div>{{user.first_name}}</div>
            <div>{{user.last_name}}</div>
            <div>
                <span v-if="!edit.title">{{user.title}} <a href="#" @click="edit.title = true">Edit</a></span>
                <span v-if="edit.title"><v-text-field v-model="title" prepend-icon="person" label="Title" type="text"></v-text-field> <a href="#" @click="confirm('title')">Confirm</a></span>
            </div>
            <div>
                <span v-if="!edit.email">{{user.email}} <a href="#" @click="edit.email = true">Edit</a></span>
                <span v-if="edit.email"><v-text-field v-model="user.email" prepend-icon="person" label="Email" type="email"></v-text-field> <a href="#" @click="confirm('email')">Confirm</a></span>
            </div>
            <div>
                <span v-if="!edit.phone">{{user.phone}} <a href="#" @click="edit.phone = true">Edit</a></span>
                <span v-if="edit.phone"><v-text-field v-model="user.phone" prepend-icon="person" label="Phone" type="text"></v-text-field> <a href="#" @click="confirm('phone')">Confirm</a></span>
            </div>
            <div>
                <span v-if="!edit.callsign">{{user.callsign}} <a href="#" @click="edit.callsign = true">Edit</a></span>
                <span v-if="edit.callsign"><v-text-field v-model="user.callsign" prepend-icon="person" label="callsign" type="text"></v-text-field> <a href="#" @click="confirm('callsign')">Confirm</a></span>
            </div>
            <div>
                <span v-if="!edit.room_number">{{user.room_number}} <a href="#" @click="edit.room_number = true">Edit</a></span>
                <span v-if="edit.room_number"><v-text-field v-model="user.room_number" prepend-icon="person" label="Room Number" type="text"></v-text-field> <a href="#" @click="confirm('room_number')">Confirm</a></span>
            </div>
            <div>{{user.graduated}}</div>
        </v-form>
    </v-container>
</template>

<script>
import moment from 'moment'
import _ from 'lodash'

export default {
    name: 'page-user-details',
    data() {
        return {
            localChange: {
                email: '',
                room_number: '',
                callsign: '',
                phone: '',
                title: ''
            },
            edit: {
                email: false,
                room_number: false,
                callsign: false,
                phone: false,
                title: false
            }
        }
    },
    props: ['userId'],
    computed: {
        user() {
            return this.$store.state.userDetails
        },
        title: {
            get () {
              return this.localChange.title || this.$store.state.userDetails.title
            },
            set (value) {
                this.localChange.title = value
            }
        }
    },
    created() {
        console.log('created', this.userId)
        this.$store.dispatch('fetchUserDetailsData', this.userId)
    },
    methods: {
        confirm(field) {
            var payload = {
                userId: this.userId
            }
            payload[field] = value

            this.$store.dispatch('editUser', payload)
        }
    }
}
</script>

<style scoped lang="scss">
    .page-list-users {
        .user-card {
            margin-bottom: 15px;
            text-align: center;

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
