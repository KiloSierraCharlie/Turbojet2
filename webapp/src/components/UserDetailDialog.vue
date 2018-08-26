<template>
    <v-dialog class="user-details-dialog" v-model="show" max-width="700px" scrollable persistent>
        <v-card v-if="userData && groupsData">
            <v-card-media
                :src="endpoint+'media/student_photos/'+(userData.picture ? userData.picture : 'cygnet.jpg')"
                height="300px"
            />
            <v-card-text style="height: 300px;">
                <v-form enctype="multipart/form-data" ref="form">
                    <v-container fluid>
                        <v-layout row wrap>
                            <v-flex xs12 sm6>
                                <v-text-field
                                    v-model="computedData.first_name"
                                    prepend-icon="person"
                                    label="Firstname"
                                    name="firstName"
                                    type="text"
                                    v-validate="{required: true}"
                                    :error="errors.has('firstName')"
                                    :error-messages="errors.collect('firstName')"
                                    :disabled="!editMode"
                                />
                            </v-flex>
                            <v-flex xs12 sm6>
                                <v-text-field
                                    v-model="computedData.last_name"
                                    prepend-icon="person"
                                    label="Lastname"
                                    name="lastName"
                                    type="text"
                                    required
                                    v-validate="{required: true}"
                                    :error="errors.has('lastName')"
                                    :error-messages="errors.collect('lastName')"
                                    :disabled="!editMode"
                                />
                            </v-flex>
                            <v-flex xs12 sm6>
                                <v-text-field
                                    v-model="computedData.email"
                                    prepend-icon="mdi-at"
                                    label="Email"
                                    name="email"
                                    type="email"
                                    v-validate="{required: true, email:true}"
                                    :error="errors.has('email')"
                                    :error-messages="errors.collect('email')"
                                    :disabled="!editMode"
                                />
                            </v-flex>
                            <v-flex xs12 sm6>
                                <v-text-field
                                    v-model="computedData.phone"
                                    prepend-icon="mdi-cellphone-iphone"
                                    label="Phone"
                                    name="phone"
                                    type="text"
                                    v-validate="{required: true}"
                                    :error="errors.has('phone')"
                                    :error-messages="errors.collect('phone')"
                                    :disabled="!editMode"
                                />
                            </v-flex>
                            <v-flex xs12 sm6>
                                <v-text-field
                                    v-model="computedData.position"
                                    prepend-icon="mdi-account-star"
                                    label="Position"
                                    name="position"
                                    type="text"
                                    :disabled="!editMode"
                                />
                            </v-flex>
                            <v-flex xs12 sm6>
                                <v-text-field
                                    v-model="computedData.calendar_zeus_username"
                                    prepend-icon="mdi-calendar"
                                    label="Zeus Username"
                                    name="calendarZeusUsername"
                                    type="text"
                                    :disabled="!editMode"
                                />
                            </v-flex>

                            <v-flex xs12 sm6>
                                <v-text-field
                                    v-model="computedData.room"
                                    prepend-icon="mdi-map-marker"
                                    label="Room"
                                    name="room"
                                    type="text"
                                    v-validate="{required: true}"
                                    :error="errors.has('room')"
                                    :error-messages="errors.collect('room')"
                                    :disabled="!editMode"
                                />
                            </v-flex>
                            <v-flex xs12 sm6>
                                <v-select
                                    :items="groupsData"
                                    v-model="computedData.groups"
                                    prepend-icon="mdi-account-group"
                                    label="Course"
                                    name="course"
                                    single-line
                                    :disabled="!editMode"
                                    multiple
                                />
                            </v-flex>
                            <v-flex xs12 sm6 v-show="isSameUser || hasPermissions" >
                                <v-text-field
                                    v-model="computedData.password"
                                    prepend-icon="lock"
                                    label="New password"
                                    name="password"
                                    ref="password"
                                    type="password"
                                    v-validate="{min:6}"
                                    :error="errors.has('password')"
                                    :error-messages="errors.collect('password')"
                                    :disabled="!editMode"
                                />
                            </v-flex>
                            <v-flex xs12 sm6 v-show="isSameUser || hasPermissions" >
                                <v-text-field
                                    v-model="computedData.confirmPassword"
                                    prepend-icon="lock"
                                    label="Confirm password"
                                    name="passwordConfirm"
                                    type="password"
                                    v-validate="{min:6, confirmed: 'password', required: computedData.password ? true : false}"
                                    :error="errors.has('passwordConfirm')"
                                    :error-messages="errors.collect('passwordConfirm')"
                                    :disabled="!editMode"
                                />
                            </v-flex>
                            <v-flex v-show="isSameUser || hasPermissions" xs12 class="mt-3">
                                <h3 class="subheading">Notifications preferences</h3>
                                <v-switch
                                    v-model="computedData.notification_news" color="indigo" hide-details
                                    label="I want to receive emails for new announcements"
                                    :disabled="!editMode"
                                />
                                <v-switch
                                    v-model="computedData.notification_ftebay" color="indigo" hide-details
                                    label="I want to receive emails for new ftebay offers"
                                    :disabled="!editMode"
                                />
                                <v-switch
                                    v-model="computedData.notification_zeus" color="indigo" hide-details
                                    label="I want to receive emails for new zeus events"
                                    :disabled="!editMode"
                                />
                            </v-flex>
                            <!-- <v-flex xs12>
                                <file-drop ref="fileDrop" label="Your picture (we must be able to see your face): *" allowed-types="images" />
                            </v-flex> -->
                        </v-layout>
                    </v-container>
                </v-form>
            </v-card-text>
             <v-divider></v-divider>
            <v-card-actions>
                <v-spacer></v-spacer>
                <!-- Event detail -->
                <div v-if="!editMode">
                    <v-btn :disabled="loading" outline color="primary" @click="closeDialog">Close</v-btn>
                    <v-btn v-show="hasPermissions" :disabled="loading" color="error" @click="deleteDialog">Delete</v-btn>
                    <v-btn v-show="hasPermissions || isSameUser" :loading="loading" color="primary" @click="setEditMode(true)">Edit</v-btn>
                </div>
                <!-- Edit event -->
                <div v-else-if="editMode">
                    <v-btn :disabled="loading" outline color="primary" @click="setEditMode(false)">Back</v-btn>
                    <v-btn :loading="loading" color="primary" @click="save">Save</v-btn>
                </div>
            </v-card-actions>
        </v-card>
    </v-dialog>
</template>

<script>
import _ from 'lodash'
import Config from 'src/Config.__ENV__.js'

export default {
    props: ['show', 'user-data', 'loading', 'groups-data','has-permissions', 'is-same-user'],
    name: 'booking-event',
    data () {
        return {
            editedUserData: null,
            isLoading: false,
            isLoadingMessage: 'Calculating ...',
            editMode: false
        }
    },
    computed: {
        endpoint() {
            return Config.endpoint
        },
        computedData() {
            return this.editMode ? this.editedUserData : this.userData
        }
    },
    methods: {
        setEditMode(bool) {
            if(bool) {
                this.editedUserData = _.cloneDeep(this.userData)
            }
            else {
                this.editedUserData = null
            }

            this.editMode = bool
        },
        closeDialog() {
            this.setEditMode(false)
            this.$emit('closeDialogEdit')
        },
        deleteDialog() {
            this.$emit('deleteUser')
        },
        save() {
            const $this = this

            this.$validator.validateAll()
                .then(function(res) {
                    if(res) {
                        $this.$emit('saveUser', _.cloneDeep($this.editedUserData))
                        $this.setEditMode(false)
                    }
                })
        },
        resetForm() {
            this.$refs.form.reset()
            this.$validator.reset()
            this.downloadedPrice = -1
        }
    }
}
</script>

<style scoped lang="scss">
    .user-details-dialog {

    }
</style>
