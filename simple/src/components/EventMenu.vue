<template>
    <v-menu class="event-menu" transition="slide-y-transition" auto>
        <a slot="activator" href="#"></a>
        <v-list>
            <v-list-tile @click="editBooking">
                <v-list-tile-action><v-icon>mdi-pencil</v-icon></v-list-tile-action>
                <v-list-tile-title>Edit</v-list-tile-title>
            </v-list-tile>
            <v-list-tile @click="deleteBooking">
                <v-list-tile-action><v-icon>mdi-delete</v-icon></v-list-tile-action>
                <v-list-tile-title>Delete</v-list-tile-title>
            </v-list-tile>
        </v-list>
    </v-menu>
</template>

<script>
import _ from 'lodash'

export default {
    props: ['editBooking', 'deleteBooking'],
    name: 'event-menu',
    data () {
        return {

        }
    },
    methods: {

    }
}
</script>

<style scoped lang="scss">
    .event-menu {
        display: block;
        position: absolute;
        z-index: 5;
        top: 0;
        bottom: 0;
        left: 0;
        right: 0;

        div.menu__activator {
            height: 100%;
        }

        a {
            display: block;
            height: 100%;
        }
    }
</style>
