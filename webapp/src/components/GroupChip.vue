<template>
    <v-chip class="group-chip" :color="color" text-color="white" small label :close="close" @input="onClose">
        <v-avatar>
            <v-icon small>{{icon}}</v-icon>
        </v-avatar>
        {{groupName}}
    </v-chip>
</template>

<script>

export default {
    props: ['group-name', 'group-type', 'close'],
    name: 'group-chip',
    computed: {
        color() {
            switch (this.groupType) {
                case 'Student Committee':
                    return 'yellow darken-2'
                    break

                case 'ATPL(A) Integrated':
                case 'ATPL(A) Integrated & MPL':
                    return 'light-blue'
                    break

                case 'Canteen Staff':
                    return 'deep-orange';
                    break

                case 'Grounds Maintenance':
                case 'Aircraft Engineering':
                    return 'green darken-1'
                    break

                case 'Ground Instructors':
                    return 'purple lighten-1'
                    break

                case 'Flight Instructors':
                    return 'teal'
                    break

                case 'FTE Administration':
                    return 'blue-grey'
                    break

                case 'I.T Department':
                    return 'brown darken-1'
                    break

                case 'ATC':
                case 'NATS programme':
                    return 'deep-purple lighten-1'
                    break

                case 'Modular course':
                case 'Modular':
                    return 'indigo';
                    break

                default:
                    return 'grey';

            }
        },
        icon() {
            switch (this.groupType) {
                case 'Student Committee':
                    return 'mdi-star-circle'
                    break

                case 'ATPL(A) Integrated':
                case 'ATPL(A) Integrated & MPL':
                    return 'mdi-plane-shield'
                    break

                case 'Canteen Staff':
                    return 'mdi-food-fork-drink';
                    break

                case 'Grounds Maintenance':
                case 'Aircraft Engineering':
                    return 'mdi-wrench'
                    break

                case 'Ground Instructors':
                    return 'mdi-school'
                    break

                case 'Flight Instructors':
                    return 'mdi-airplane'
                    break

                case 'FTE Administration':
                    return 'mdi-deskphone'
                    break

                case 'I.T Department':
                    return 'mdi-desktop-classic'
                    break

                case 'ATC':
                case 'NATS programme':
                    return 'mdi-airport'
                    break

                case 'Modular':
                    return 'mdi-plane-shield'
                    break

            }
        }
    },
    methods: {
        onClose() {
            this.$emit('remove')
        }
    }
}
</script>

<style scoped lang="scss">
@import '~scss/variables';

.group-chip {
    &.v-chip .v-avatar {
        margin-left: -15px;
        margin-right: 0px;
    }

}
</style>
