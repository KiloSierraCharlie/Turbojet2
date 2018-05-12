<template>
    <v-container fluid fill-height class="page-login pa-0">
        <v-layout>
            <v-flex xs12 sm6 md4 lg3 >
                <div class="side-login">
                    <div class="logo">
                        <img src="/public/logo.png" alt="turbojet logo"/>
                    </div>
                    <h1 class="headline">{{$t('pagesTitle.page-login')}}</h1>
                    <p class="">{{$t('login.welcome')}}</p>
                    <p class="error-msg red--text" v-if="errorMsg">{{$t('generic.error') + ':&nbsp;' + errorMsg}}</p>
                    <v-form>
                        <v-text-field v-model="email" prepend-icon="person" :label="$t('login.email')" type="email" required></v-text-field>
                        <v-text-field v-model="password" prepend-icon="lock" :label="$t('login.password')" type="password" required></v-text-field>
                        <div class="text-xs-center">
                            <v-btn outline color="primary" @click="goRegister">Register</v-btn>
                            <v-btn color="primary" @click="onLogin">Login</v-btn>
                        </div>
                    </v-form>
                </div>
            </v-flex>
            <v-flex sm6 md8 lg9 :style="'background: url(/public/login-backgrounds/'+backgroundImage+') center center / cover no-repeat'" />
        </v-layout>
        <v-dialog v-model="register" fullscreen hide-overlay transition="dialog-bottom-transition" scrollable>
            <v-card tile>
                <v-toolbar card dark color="primary">
                    <v-btn icon @click.native="register = false" dark>
                        <v-icon>close</v-icon>
                    </v-btn>
                    <v-toolbar-title>{{$t('pagesTitle.page-register')}}</v-toolbar-title>
                </v-toolbar>
                <v-card-text>
                    <v-container fluid class="pa-0">
                        <v-layout align-center justify-center>
                            <v-flex xs12 sm10 md8 lg6>
                                <v-container fluid grid-list-xl class="pa-0">
                                    <v-layout row wrap>
                                        <v-flex xs12 sm6>
                                            <v-text-field v-model="firstname" prepend-icon="person" :label="$t('login.firstname')" type="text" required></v-text-field>
                                        </v-flex>
                                        <v-flex xs12 sm6>
                                            <v-text-field v-model="lastname" prepend-icon="person" :label="$t('login.lastname')" type="text" required></v-text-field>
                                        </v-flex>
                                        <v-flex xs12 sm6>
                                            <v-text-field v-model="email" prepend-icon="person" :label="$t('login.email')" type="email" required></v-text-field>
                                        </v-flex>
                                        <v-flex xs12 sm6>
                                            <v-text-field v-model="phone" prepend-icon="person" :label="$t('login.phone')" type="email" required></v-text-field>
                                        </v-flex>
                                        <v-flex xs12 sm6>
                                            <v-text-field v-model="room" prepend-icon="person" :label="$t('login.room')" type="text" required></v-text-field>
                                        </v-flex>
                                        <v-flex xs12 sm6>
                                            <v-select :items="groups" v-model="group" prepend-icon="person" :label="$t('login.course')" single-line required></v-select>
                                        </v-flex>
                                        <v-flex xs12 sm6>
                                            <v-text-field v-model="password" prepend-icon="lock" :label="$t('login.password')" type="password" required></v-text-field>
                                        </v-flex>
                                        <v-flex xs12 sm6>
                                            <v-text-field v-model="confirmPassword" prepend-icon="lock" :label="$t('login.confirm-password')" type="password" required></v-text-field>
                                        </v-flex>
                                        <v-flex xs12>
                                            TODO picture
                                        </v-flex>
                                        <v-flex xs12>
                                            <vue-recaptcha :sitekey="recaptchaKey"></vue-recaptcha>
                                        </v-flex>
                                        <v-flex xs12>
                                            <v-btn outline @click="register = false">{{$t('generic.back')}}</v-btn>
                                            <v-btn color="primary" @click="onRegister">{{$t('login.register')}}</v-btn>
                                        </v-flex>
                                    </v-layout>
                                </v-container>
                            </v-flex>
                        </v-layout>
                    </v-container>
                </v-card-text>
            </v-card>
        </v-dialog>
    </v-container>
</template>

<script>
import _ from 'lodash'
import Axios from 'axios'
import Config from 'src/Config.__ENV__.js'
import VueRecaptcha from 'vue-recaptcha'

export default {
    data() {
        return {
            register: false,
            groups: [],
            firstname: '',
            lastname: '',
            room: '',
            group: '',
            email: '',
            phone: '',
            password: '',
            confirmPassword: '',
            isLoading: false,
            errorMsg: ''
        }
    },
    computed: {
        backgroundImage() {
            return _.sample([
                'stock-photo-14127925.jpg',
                'stock-photo-146305243.jpg',
                'stock-photo-202413751.jpg',
                'stock-photo-223792207.jpg',
                'stock-photo-227877121.jpg',
                'stock-photo-241687465.jpg'
            ])
        },
        recaptchaKey() {
            return Config.recaptchaKey
        }
    },
    name: 'page-login',
    methods: {
        goRegister(e) {
            this.register = true
        },

        onRegister(e) {

        },

        /**
        * Submission of the login form
        * @param Event e the DOM event
        */
        onLogin(e) {
            var self = this

            if (this.isLoading) {
                return
            }

            this.isLoading = true

            var formData = {
                username: this.email,
                password: this.password
            }

            this.$store.dispatch('authUser', formData)
                .then(function (response) {
                    console.log('login success', response)
                    self.isLoading = false
                })
                .catch(function (error) {
                    console.log('login error', error)
                    self.isLoading = false

                    // error received from the server
                    if (error.response && _.has(error, 'response.data.message')) {
                        self.errorMsg = error.response.data.message
                    }
                    // no answer from the server, or no error message in the body
                    else {
                        self.errorMsg = self.$t('login.errors.unknownError')
                    }
                })
        },

        getPicklistData() {
            var self = this
            Axios.get(Config.endpoint + 'picklists/groups')
                .then(function (response) {
                    self.groups = response.data
                })
                .catch(function (error) {
                    // TODO manage error
                    console.log(error);
                });

        },
        initCaptcha() {
            // console.log('this.$refs', this.$refs.grecaptcha)
            // window.grecaptcha.render('g-recaptcha', {"sitekey": "6LeS8k8UAAAAAOZsYkWOV9dSei8RNa2sSDFNI6hB"})
        }
    },
    created() {
        this.getPicklistData()
        this.initCaptcha()
    },
    components: {
        VueRecaptcha
    }
}
</script>

<style scoped lang="scss">
@import '~scss/variables';

.page-login {
    .logo {
        text-align: center;

        img {
            width: 215px;
        }
    }

    .side-login {
        // background-color: rgba(255,255,255,0.5);
        background-color: white;
        height: 100%;
        padding: 25px;
    }

    .error-msg {
        margin-bottom: 10px;
    }
}
</style>
