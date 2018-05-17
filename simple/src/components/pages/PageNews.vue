<template>
    <v-container fluid class="page-news">
        <v-layout row>
            <v-flex xs12>

            </v-flex xs12>
        </v-layout row>
    </v-container>
</template>

<script>
import moment from 'moment'
import _ from 'lodash'

export default {
    name: 'page-news',
    data() {
        return {

        }
    },
    computed: {

    },
    created() {
        this.$store.dispatch('fetchNewsData')
    },
    methods: {

    }
}
</script>

<style scoped lang="scss">
    .page-news {
    }
</style>
