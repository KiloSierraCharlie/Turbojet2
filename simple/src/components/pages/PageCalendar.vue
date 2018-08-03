<template>
    <v-container fluid class="page-booking">
        <v-layout row>
            <v-flex xs12>
                <full-calendar ref="calendar" :event-sources="eventSources" :config="calendarConfig"></full-calendar>
            </v-flex xs12>
        </v-layout row>
        <router-view ref="dialog"
            :event-data="editedEvent"
            :show="dialogEdit"
            :mark-as-paid-button="$route.meta.api.markAsPaid"
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
          <v-btn dark flat @click.native="snackbar = false">Close</v-btn>
        </v-snackbar>
    </v-container>
</template>

<script>
import Vue from 'vue'
import Axios from 'axios'
import moment from 'moment'
import _ from 'lodash'
import Config from 'src/Config.__ENV__.js'
import EventMenu from 'components/EventMenu.vue'

export default {
    name: 'page-calendar',
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
                defaultView: 'agendaWeek',
                timezone: 'Europe/Madrid',
                allDaySlot: false,
                selectable: false,
                nowIndicator: true,
                height: 'auto',
                navLinks: true
            },
            eventSources: [],
            snackbar: false,
            errorMessage: ''
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
        displayError(error) {
            this.isLoading = false

            console.log('error', error)

            if(_.has(error, 'message')) {
                this.errorMessage = error.message
                this.snackbar = true
            }
            else {
                this.errorMessage = 'An error occured, please try again'
                this.snackbar = true
            }
        },

        fetchEvents() {
            const $this = this

            console.log('fetchEvents')

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

                                if($this.$route.meta.settings.multiResources) {
                                    // TODO bug when barbecueue -> minivan
                                    $this.$refs.calendar.fireMethod('changeView', 'agendaDay')

                                    $this.$refs.calendar.fireMethod('option', {
                                        groupByResource: true,
                                        resources: function(callbackResources, start, end, timezone) {
                                            // Axios.get(Config.endpoint + this.$route.meta.api.getResources) // TODO
                                            Axios.get(Config.endpoint + 'bookings/barbecue/resources')
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
                                                    // TODO manage error
                                                    console.log(error);
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
                                // TODO manage error
                                console.log(error);
                            });
                    }
                }
            ]
        }
    },
    mounted() {
        this.fetchEvents()
    }
}
</script>

<style scoped lang="scss">
    .page-calendar {

    }
</style>
