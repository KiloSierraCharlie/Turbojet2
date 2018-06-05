<template>
    <v-container fluid class="page-ftebay">
        <v-layout row>
            <v-flex xs12>
                <v-card v-for="(post, index) in posts" :key="post.id" class="mt-4">
                    <v-card-media
                        :class="'pt-4 white--text ' + randomColor()"
                        height="200px"
                        src="/static/doc-images/cards/docks.jpg"
                    >
                        <v-container fill-height fluid>
                            <v-layout fill-height>
                                <v-flex xs12 align-end flexbox>
                                  <span class="headline">{{post.title}}</span>
                                </v-flex>
                            </v-layout>
                        </v-container>
                    </v-card-media>
                    <v-card-title>
                        <div>
                            <div class="mb-2 grey--text text--darken-2">
                                <span><v-icon small class="mr-1">mdi-calendar-text</v-icon>{{formatDate(post.date)}}</span>
                                <span class="ml-2"><router-link :to="'user/'+post.id_user"><v-icon small class="mr-1">mdi-account</v-icon>{{post.name}}</router-link></span>
                            </div>
                            <div v-html="post.content"></div>
                        </div>
                    </v-card-title>
                    <!-- <v-card-actions>

                    </v-card-actions> -->
                </v-card>
            </v-flex xs12>
        </v-layout row>
        <!-- <v-layout row class="mt-3">
            <v-flex xs12>
                <div class="text-xs-center">
                    <v-pagination
                        circle :length="totalPages" v-model="currentPage"
                        total-visible="7" @input="onPageChange" />
                </div>
            </v-flex>
        </v-layout> -->
    </v-container>
</template>

<script>
import Axios from 'axios'
import moment from 'moment'
import _ from 'lodash'
import Config from 'src/Config.__ENV__.js'

export default {
    name: 'page-ftebay',
    data() {
        return {
            posts: [],
            // currentPage: 1,
            // totalToDisplay: 10,
            // totalPages: 0,
            newsColors: [
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
    computed: {
    },
    created() {
        this.fetchPostsData()
    },
    methods: {
        fetchPostsData() {
            var that = this

            // this.$store.dispatch('fetchNewsData')
            Axios.get(Config.endpoint + 'ftebay-posts')
                .then(function (response) {
                    that.posts = response.data
                    // that.totalPages = _.toInteger(response.data.totalPages)
                    console.log('fetch news data success', response.data)
                })
                .catch(function (error) {
                    // TODO manage error
                    console.log(error);
                });
        },
        // onPageChange() {
        //     this.fetchNewsData()
        //     window.scrollTo(0, 0)
        // },
        randomColor() {
            return _.sample(this.newsColors)
        },
        formatDate(date) {
            return moment(date).format("dddd, MMMM Do YYYY, h:mm a")
        }
    }
}
</script>

<style scoped lang="scss">
    .page-news {
    }
</style>
