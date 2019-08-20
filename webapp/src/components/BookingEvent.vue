<template>
    <v-dialog class="booking-event" v-model="show" max-width="500px" scrollable persistent>
        <v-card>
            <v-card-title>
                <span class="headline">{{ formTitle }}</span>
            </v-card-title>
            <v-divider></v-divider>
            <v-card-text style="height: 300px;">
                <v-form enctype="multipart/form-data" ref="form">
                    <v-text-field
                        v-if="eventData.data.id"
                        v-model="eventData.data.id"
                        label="Booking ID"
                        disabled
                    />
                    <v-text-field
                        v-if="eventData.data.user_name"
                        v-model="eventData.data.user_name"
                        label="Booked by"
                        disabled
                    />
                    <v-text-field
                        v-model="eventData.start.format('MMMM Do YYYY, h:mm a')"
                        label="Start at"
                        disabled
                    />
                    <v-text-field
                        v-model="eventData.end.format('MMMM Do YYYY, h:mm a')"
                        label="End at"
                        disabled
                    />
                    <v-text-field
                        v-if="eventData.data.price || eventData.priceIsLoading"
                        v-model="eventData.data.price + ' €'"
                        label="Price"
                        disabled
                        :loading="eventData.priceIsLoading"
                    />
                    <v-text-field
                        v-model="eventData.data.booking_reason"
                        :label="bookingReasonLabel"
                        name="booking_reason"
                        v-validate="{required: true}"
                        :error="errors.has('booking_reason')"
                        :error-messages="errors.collect('booking_reason')"
                        :disabled="eventData.data.id && !editMode ? true : false"
                    />
                </v-form>
            </v-card-text>
            <v-divider></v-divider>
            <v-card-actions>
                <v-spacer></v-spacer>
                <!-- Event detail -->
                <div v-if="eventData.data.id && !editMode">
                    <v-btn :disabled="loading" outline color="primary" @click="closeDialog">Close</v-btn>
                    <v-btn v-show="hasPermissions" :disabled="loading" color="error" @click="cancelDialog">Cancel</v-btn>
                    <v-btn v-show="hasPermissions" :loading="loading" color="primary" @click="editMode = true;">Edit</v-btn>
                    <v-btn v-show="hasPermissions && markAsPaidButton && !eventData.data.paid" :loading="loading" color="success" @click="paid">Mark as paid</v-btn>
                </div>
                <!-- Edit event -->
                <div v-else-if="eventData.data.id && editMode">
                    <v-btn :disabled="loading" outline color="primary" @click="editMode = false;">Back</v-btn>
                    <v-btn :loading="loading" color="primary" @click="save">Save</v-btn>
                </div>
                <!-- New event -->
                <div v-else>
                    <v-btn :disabled="loading" outline color="primary" @click="closeDialog">Close</v-btn>
                    <v-btn :loading="loading" color="primary" @click="save">Save</v-btn>
                </div>
            </v-card-actions>
        </v-card>
    </v-dialog>
</template>

<script>
import _ from 'lodash'

export default {
    props: ['show', 'event-data', 'loading', 'mark-as-paid-button', 'has-permissions', 'booking-reason-label'],
    name: 'booking-event',
    data () {
        return {
            isLoading: false,
            isLoadingMessage: 'Calculating ...',
            editMode: false
        }
    },
    computed: {
        formTitle () {
            if(!this.eventData.data.id) {
                return 'New booking'
            }
            else if(this.eventData.data.id && ! this.editMode) {
                return 'View booking'
            }
            else if(this.eventData.data.id && this.editMode) {
                return 'Edit booking'
            }
        }
    },
    methods: {
        closeDialog() {
            this.editMode = false
            this.$emit('closeDialogEdit')
        },
        cancelDialog() {
            this.$emit('cancelBooking')
        },
        paid() {
            this.$emit('bookingPaid')
        },
        save() {
            const $this = this

            this.$validator.validateAll()
                .then(function(res) {
                    if(res) {
                        $this.editMode = false
                        $this.$emit('saveBooking')
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

<style scoped lang="scss"></style>
