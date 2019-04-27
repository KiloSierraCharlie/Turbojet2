<template>
    <v-container fluid class="page-booking">
        <v-layout row class="mb-3">
            <v-flex xs12>
                <v-alert :value="true" color="info" icon="info" outline>
                    To book an event from a touch device: Press and hold the box you would like your event to start from, wait 1 second then slide your finger downward in order to extend the timeslot. Release your finger when you're happy.
                </v-alert>
            </v-flex>
        </v-layout>
        <v-layout row>
            <v-flex xs12>
                <full-calendar ref="calendar" :event-sources="eventSources" :config="calendarConfig"></full-calendar>
            </v-flex xs12>
        </v-layout row>
        <router-view ref="dialog"
            :event-data="editedEvent"
            :show="dialogEdit"
            :booking-reason-label="$route.meta.labels.bookingReason"
            :mark-as-paid-button="$route.meta.api.markAsPaid"
            :has-permissions="connectedUser ? connectedUser.id === editedEvent.data.id_user || connectedUser.hasPermissions('permission_edit_booking') : false"
            @saveBooking="onSaveBooking"
            @closeDialogEdit="onCloseDialogEdit"
            @bookingPaid="onBookingPaid"
            @cancelBooking="dialogCancel = true;"
            :loading="isLoading">
        </router-view>
        <v-dialog v-model="dialogCancel" max-width="500px" persistent>
            <v-card>
                <v-card-title class="headline">Confirm cancellation ?</v-card-title>
                <v-card-text>Are you sure you want to cancel this booking?</v-card-text>
                <v-card-actions>
                    <v-spacer></v-spacer>
                    <v-btn :disabled="isLoading" outline color="primary" @click="dialogCancel = false;">Cancel</v-btn>
                    <v-btn :loading="isLoading" color="primary" @click="cancelBooking">Confirm</v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>
        <v-snackbar :timeout="0" color="red accent-2" v-model="snackbar">
          {{ errorMessage }}
          <v-btn dark flat @click.native="snackbar = false; errorMessage = ''">Close</v-btn>
        </v-snackbar>
    </v-container>
</template>

<script>
import Vue from 'vue'
import Axios from 'axios'
// import moment from 'moment'
import moment from 'moment-timezone'
import _ from 'lodash'
import Config from 'src/Config.__ENV__.js'

export default {
    name: 'page-booking',
    props: ['bookingType'],
    data() {
        return {
            windowHeight: 0,
            calendarConfig: {
                schedulerLicenseKey: 'CC-Attribution-NonCommercial-NoDerivatives',
                header: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'agendaWeek,agendaDay',
                },
                footer: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'agendaWeek,agendaDay',
                },
                defaultView: this.$route.meta.settings.multiResources ? 'agendaDay' : 'agendaWeek',
                timezone: 'Europe/Madrid',
                allDaySlot: false,
                selectable: true,
                nowIndicator: true,
                height: 'auto',
                navLinks: true,
                selectOverlap: this.$route.meta.settings.multiResources,
                select: this.onDateSelect,
                eventClick: this.onEventClick
            },
            eventSources: [],
            // resourcesSource: null,
            dialogEdit: false,
            dialogCancel: false,
            editedEvent: {
                start: moment(),
                end: moment(),
                priceIsLoading: false,
                data: {
                    id: '',
                    id_resource: '',
                    id_user: '',
                    user_name: '',
                    price: '',
                    paid: false,
                    booking_reason: ''
                }
            },
            isLoading: false,
            snackbar: false,
            errorMessage: ''
        }
    },
    watch: {
        '$route': 'fetchData',
    },
    computed: {
        _() {
            return _
        },
        connectedUser() {
            return this.$store.state.connectedUser
        }
    },
    methods: {
        getEventColor(start, end) {
            const now = moment()

            if(now >= moment(start) && now <= moment(end)) {
                return '#F44336'
            }
            else if(now > moment(end)) {
                return '#757575'
            }
            else {
                return '#3f51b5'
            }
        },
        onDateSelect(start, end, jsEvent, view, resource) {
            console.log('select', start.format(), end.format())
            console.log('select local', start.local().format(), end.local().format())

            if(_.has(this.$route, 'meta.settings.permissions') && !this.connectedUser.hasPermissions(this.$route.meta.settings.permissions)) {
                this.errorMessage = 'You don\'t have permission to create booking. Please contact the IT rep if you think this is a mistake.'
                this.snackbar = true
                return
            }

            const $this = this

            if(start.local() < moment().local()) {
                this.errorMessage = 'You can\'t book in the past!'
                this.snackbar = true
                return
            }

            this.editedEvent.start = start

            // Minimum renting time
            var duration = moment.duration(end.diff(start))
            var hours = duration.asHours()

            if(hours < this.$route.meta.settings.minimumHours) {
                this.editedEvent.end = moment(start).add(this.$route.meta.settings.minimumHours, 'hours')
            }
            else if(hours > this.$route.meta.settings.maximumHours) {
                this.errorMessage = "Maximum booking length is " + this.$route.meta.settings.maximumHours
                + " hours. If you need longer, contact the social commitee directly.";
                this.snackbar = true
                return
            }
            else {
                this.editedEvent.end = end
            }

            if(resource && resource.id) {
                this.editedEvent.id_resource = resource.id
            }

            if(this.$route.meta.api.getPriceApi) {
                this.editedEvent.priceIsLoading = true

                Axios.get(Config.endpoint + this.$route.meta.api.getPriceApi+'?start='+this.editedEvent.start.format('YYYY-MM-DD HH:mm:ss')+'&end='+this.editedEvent.end.format('YYYY-MM-DD HH:mm:ss'))
                    .then(function(response) {
                        $this.editedEvent.priceIsLoading = false
                        $this.editedEvent.data.price = response.data.price
                    })
                    .catch(this.displayError)
            }
            this.dialogEdit = true
        },
        onEventClick(event, jsEvent, view){
            console.log('event', event)

            this.editedEvent.start = event.start
            this.editedEvent.end = event.end

            this.editedEvent.data = {
                id: event.data.id,
                id_resource: event.data.id_resource,
                id_user: event.data.id_user,
                user_name: event.data.user_name,
                price: event.data.price,
                paid: event.data.paid === "1" ? true : false,
                booking_reason: event.data.booking_reason
            }

            this.dialogEdit = true
        },
        onCloseDialogEdit() {
            const $this = this

            this.dialogEdit = false
            this.snackbar = false

            setTimeout(() => {
                $this.resetForm()
            }, 300)
        },
        onSaveBooking() {
            const $this = this

            this.isLoading = true

            var onSuccess = function(response) {
                $this.isLoading = false
                $this.onCloseDialogEdit()
                $this.$refs.calendar.fireMethod('refetchEvents')
            }

            // New post / link => POST request
            if(!this.editedEvent.data.id) {
                var payload = {
                    start: this.editedEvent.start.format('YYYY-MM-DD HH:mm:ss'),
                    end: this.editedEvent.end.format('YYYY-MM-DD HH:mm:ss'),
                    bookingReason: this.editedEvent.data.booking_reason,
                    resourceId: this.editedEvent.id_resource
                }

                Axios.post(Config.endpoint + this.$route.meta.api.post, payload)
                    .then(onSuccess)
                    .catch(this.displayError)
            }

            // Edit existing post / link => PUT request
            else {
                var payload = {
                    id: this.editedEvent.data.id,
                    bookingReason: this.editedEvent.data.booking_reason
                }

                Axios.post(Config.endpoint + this.$route.meta.api.edit.replace('{id}', this.editedEvent.data.id), payload)
                    .then(onSuccess)
                    .catch(this.displayError)
            }

            this.isLoading = true

            console.log('editBooking', this.editedEvent)
        },
        cancelBooking() {
            const $this = this

            Axios.post(Config.endpoint + this.$route.meta.api.changeState.replace('{id}', this.editedEvent.data.id), {cancelled: true})
                .then(function(response) {
                    $this.isLoading = false
                    $this.onCloseDialogEdit()
                    $this.dialogCancel = false
                    $this.$refs.calendar.fireMethod('refetchEvents')
                })
                .catch(this.displayError)
        },
        onBookingPaid() {
            const $this = this

            Axios.post(Config.endpoint + this.$route.meta.api.markAsPaid.replace('{id}', this.editedEvent.data.id))
                .then(function(response) {
                    $this.isLoading = false
                    $this.onCloseDialogEdit()
                    $this.$refs.calendar.fireMethod('refetchEvents')
                })
                .catch(this.displayError)
        },
        resetForm() {
            this.$refs.dialog.resetForm()
            this.editedEvent.start = moment()
            this.editedEvent.end = moment()
            this.editedEvent.getPriceApi = ''
            this.editedEvent.priceIsLoading = false

            // TODO use assign and default object
            this.editedEvent.data = {
                id: '',
                id_resource: '',
                user_name: '',
                price: '',
                paid: false,
                booking_reason: ''
            }
        },

        displayError(error) {
            this.isLoading = false

            console.log('error', error)

            if(_.has(error, 'response.data.message')) {
                this.errorMessage = error.response.data.message
                this.snackbar = true
            }
            else {
                this.errorMessage = 'An error occured, please try again'
                this.snackbar = true
            }
        },

        fetchData() {
            const $this = this

            console.log('fetchData')

            this.eventSources = [
                {
                    events(start, end, timezone, callbackEvents) {
                        Axios.get(Config.endpoint + $this.$route.meta.api.getAll +'?dateFrom='+moment(start).format()+'&dateTo='+moment(end).format())
                            .then(function (response) {
                                var events = _.map(response.data, function(item) {
                                    return {
                                        title: ($this.$route.meta.settings.multiResources ? item.resource_name + '\n' : '') + item.user_name+'\n'+item.booking_reason,
                                        start: moment(item.start).format(),
                                        end: moment(item.end).format(),
                                        allDay: false,
                                        editable: false,
                                        color: $this.getEventColor(item.start, item.end),
                                        data: item,
                                        resourceId: item.id_resource
                                    }
                                })

                                // if($this.$route.meta.settings.multiResources) {
                                if($this.$route.meta.api.getResources) {
                                    // TODO bug when barbecueue -> minivan -> book event

                                    if($this.$route.meta.settings.multiResources) {
                                        $this.$refs.calendar.fireMethod('changeView', 'agendaDay')
                                    }
                                    else {
                                        $this.$refs.calendar.fireMethod('changeView', 'agendaWeek')
                                    }

                                    $this.$refs.calendar.fireMethod('option', {
                                        groupByResource: true,
                                        resources: function(callbackResources, start, end, timezone) {
                                            Axios.get(Config.endpoint + $this.$route.meta.api.getResources)
                                                .then(function (response) {
                                                    callbackResources(_.map(response.data, function(item) {
                                                        return {
                                                            id: item.id,
                                                            title: item.name
                                                        }
                                                    }))

                                                    callbackEvents(events)
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
                                                })
                                        }
                                    })

                                    $this.$refs.calendar.fireMethod('refetchResources')
                                }
                                else {
                                    $this.$refs.calendar.fireMethod('changeView', 'agendaWeek')
                                    $this.$refs.calendar.fireMethod('option', {
                                      groupByResource: false,
                                      resources: null
                                    })

                                    callbackEvents(events)
                                }
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
                    }
                }
            ]
        }
    },
    mounted() {
        console.log('this.$route', this.$route)

        this.fetchData()
    }
}
</script>

<style scoped lang="scss"></style>
