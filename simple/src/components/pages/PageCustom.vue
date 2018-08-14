<template>
    <v-container fluid class="page-list-editorial">
        <v-layout row>
            <v-flex xs12>
                <v-btn color="primary" @click="goPageManager">Page Manager</v-btn>
                <v-card v-if="page">
                    <v-card-title
                        :class="'white--text ' + randomColor()"
                        src="/static/doc-images/cards/docks.jpg"
                    >
                        <span class="headline">{{page.title}}</span>
                    </v-card-title>
                    <v-card-text>
                        <div>
                            <div v-html="page.content"></div>
                        </div>
                    </v-card-text>
                </v-card>

            </v-flex xs12>
        </v-layout row>
    </v-container>
</template>

<script>
import Axios from 'axios'
import moment from 'moment'
import _ from 'lodash'
import Config from 'src/Config.__ENV__.js'

export default {
    name: 'page-list-editorial',
    props: ['section', 'id'],
    data() {
        return {
            page: null,
            colors: [
                'pink',
                'indigo',
                'red',
                'purple',
                'blue',
                'deep-purple ',
                'light-blue ',
                'teal',
                'green',
                'amber',
                'blue-grey'
            ]
        }
    },
    created() {
        this.fetchData()
    },
    watch: {
        '$route': 'fetchData'
    },
    methods: {
        fetchData() {
            const $this = this

            Axios.get(Config.endpoint + 'editorial/' + this.$props.section + '/' + this.$props.id)
                .then(function (response) {
                    $this.page = response.data
                })
                .catch(function (error) {
                    if(_.has(error, 'response.data.message')) {
                        $this.errorMessage = error.response.data.message
                        $this.snackbar = true
                    }
                    else {
                        $this.errorMessage = 'An error occured, please try again'
                        $this.snackbar = true
                    }
                });
        },
        formatDate(date) {
            return moment(date).format("dddd, MMMM Do YYYY, h:mm a")
        },
        onPageChange() {
            this.fetchData()
            window.scrollTo(0, 0)
        },
        randomColor() {
            return _.sample(this.colors)
        },
        goPageManager() {
            this.$router.push({ path: '/page-manager/'+this.$props.section })
        }
    }
}
</script>

<style scoped lang="scss">
</style>
