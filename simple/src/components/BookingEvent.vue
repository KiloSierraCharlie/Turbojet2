<template>
    <v-dialog class="booking-event" v-model="show" max-width="500px" persistent>
        <v-card>
            <v-form enctype="multipart/form-data" ref="form" v-model="formIsValid">
                <v-card-title>
                    <span class="headline">{{ formTitle }}</span>
                </v-card-title>
                <v-card-text>
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
                        label="Trip location"
                        required
                        :rules="[rules.required]"
                        :disabled="eventData.data.id && !editMode ? true : false"
                    />
                </v-card-text>
                <v-card-actions>
                    <v-spacer></v-spacer>
                    <!-- Event detail -->
                    <div v-if="eventData.data.id && !editMode">
                        <v-btn :disabled="loading" outline color="primary" @click="closeDialog">Close</v-btn>
                        <v-btn :disabled="loading" color="error" @click="cancelDialog">Cancel</v-btn>
                        <v-btn :loading="loading" color="primary" @click="editMode = true;">Edit</v-btn>
                        <v-btn v-if="markAsPaidButton && !eventData.data.paid" :loading="loading" color="success" @click="paid">Mark as paid</v-btn>
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
            </v-form>
        </v-card>
    </v-dialog>
</template>

<script>
import _ from 'lodash'

export default {
    props: ['show', 'event-data', 'loading', 'mark-as-paid-button'],
    name: 'booking-event',
    data () {
        return {
            formIsValid: false,
            isLoading: false,
            isLoadingMessage: 'Calculating ...',
            rules: {
                required(value) {
                    return !!value || 'Required.'
                }
            },
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
            this.$refs.form.reset()
            this.$emit('closeDialogEdit')
        },
        cancelDialog() {
            if (this.$refs.form.validate()) {
                this.$emit('cancelBooking')
            }
        },
        paid() {
            this.$emit('bookingPaid')
        },
        save() {
            if (this.$refs.form.validate()) {
                this.editMode = false
                this.$emit('saveBooking')
            }
        },
        resetForm() {
            this.$refs.form.reset()
            this.downloadedPrice = -1
        }
    }
}
</script>

<style scoped lang="scss">
    .booking-event {

    }
</style>
