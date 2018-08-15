<template>
    <v-container fluid class="page-my-zeus-calendar pt-0">
        <v-layout row class="mb-3">
            <v-flex xs12>
                <v-alert :value="true" color="error" icon="warning" outline>
                  ALL EVENTS ARE LOCAL TIME.<br>
                  To configure your ZEUS calendar, please complete your zeus username in <a @click="openUserProfile">your profile pages</a>.
                </v-alert>
            </v-flex>
        </v-layout>
        <!-- <v-layout row v-if="connectedUser">
            <v-flex xs12>
                <v-card class="pa-3 mb-4">
                    <v-layout row wrap>
                        <v-flex xs12>
                            You can subscribe to this calendar with your device. You will then be able to receive directly the events on your device.<br>
                            To subscribe to this calendar: <a href="webcal://fteturbojet.com/calendar/ical/685/">Click here</a> or copy and past this link in your web browser <strong>webcal://api.fteturbojet.com/ical/{{connectedUser.id}}</strong>
                        </v-flex>
                    </v-layout>
                </v-card>
            </v-flex xs12>
        </v-layout row> -->
        <v-layout row>
            <v-flex xs12>
                <full-calendar ref="calendar" :event-sources="eventSources" :config="calendarConfig"></full-calendar>
            </v-flex xs12>
        </v-layout row>
        <v-snackbar :timeout="0" color="red accent-2" v-model="snackbar">
          {{ errorMessage }}
          <v-btn dark flat @click.native="snackbar = false; errorMessage=''">Close</v-btn>
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
            // showSettings: false,
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

    computed: {
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
                        Axios.get(Config.endpoint + 'user/calendar' +'?dateFrom='+moment(start).format()+'&dateTo='+moment(end).format())
                            .then(function (response) {

                                var events = _.map(response.data, function(item) {
                                    return {
                                        title: item.exercise+'\n'+item.registration+'\nCPT: '+item.captain+(item.crew1 ? '\nC1: '+item.crew1 : ''),
                                        start: moment(item.start).format(),
                                        end: moment(item.end).format(),
                                        allDay: false,
                                        editable: false,
                                        color: $this.getEventColor(item.start, item.end),
                                        data: item
                                    }
                                })

                                $this.$refs.calendar.fireMethod('changeView', 'agendaWeek')
                                $this.$refs.calendar.fireMethod('option', {
                                  groupByResource: false,
                                  resources: null
                                })

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
                            });
                    }
                }
            ]
        },

        openUserProfile() {
            this.$root.$emit('showUser', this.$store.state.connectedUser.id)
        },

        onNotifSettingChange() {
            console.log('onNotifSettingChange')
        }
    },
    mounted() {
        this.fetchData()
    }
}
</script>

<style scoped lang="scss">
    .page-my-zeus-calendar {

    }
</style>
