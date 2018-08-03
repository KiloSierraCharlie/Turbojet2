<template>
    <v-container fluid fill-height class="page-login pa-0">
        <v-layout>
            <v-flex xs12 sm6 md4 lg3 >
                <div class="side-login">
                    <div class="logo">
                        <img src="/public/logo.png" alt="turbojet logo"/>
                    </div>
                    <h1 class="headline">Login</h1>
                    <p class="">Welcome on FTE Turbojet, please signin in order to continue</p>
                    <p class="error-msg red--text" v-if="errorMsg">Error {{errorMsg}}</p>
                    <v-form>
                        <v-text-field v-model="loginFormData.username" prepend-icon="person" label="Email" type="email" required></v-text-field>
                        <v-text-field v-model="loginFormData.password" prepend-icon="lock" label="Password" type="password" required></v-text-field>
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
                    <v-spacer></v-spacer>
                    <v-toolbar-title>Register</v-toolbar-title>
                    <v-spacer></v-spacer>
                    <v-btn icon @click.native="register = false" dark>
                        <v-icon>close</v-icon>
                    </v-btn>

                </v-toolbar>
                <v-card-text>
                    <v-container fluid class="pa-0">
                        <v-layout align-center justify-center>
                            <v-flex xs12 sm10 md8 lg6>
                                <v-container fluid grid-list-xl class="pa-0">
                                    <v-layout row wrap>
                                        <v-flex xs12 sm6>
                                            <v-text-field
                                                v-model="registerFormData.firstName"
                                                prepend-icon="person"
                                                label="Firstname"
                                                type="text"
                                                required
                                                :rules="[rules.required]"
                                            />
                                        </v-flex>
                                        <v-flex xs12 sm6>
                                            <v-text-field
                                                v-model="registerFormData.lastName"
                                                prepend-icon="person"
                                                label="Lastname"
                                                type="text"
                                                required
                                                :rules="[rules.required]"
                                            />
                                        </v-flex>
                                        <v-flex xs12 sm6>
                                            <v-text-field
                                                v-model="registerFormData.email"
                                                prepend-icon="person"
                                                label="Email"
                                                type="email"
                                                required
                                                :rules="[rules.required]"
                                            />
                                        </v-flex>
                                        <v-flex xs12 sm6>
                                            <v-text-field
                                                v-model="registerFormData.phone"
                                                prepend-icon="person"
                                                label="Phone"
                                                type="email"
                                                required
                                                :rules="[rules.required]"
                                            />
                                        </v-flex>
                                        <v-flex xs12 sm6>
                                            <v-text-field
                                                v-model="registerFormData.room"
                                                prepend-icon="person"
                                                label="Room"
                                                type="text"
                                                required
                                                :rules="[rules.required]"
                                            />
                                        </v-flex>
                                        <v-flex xs12 sm6>
                                            <v-select
                                                :items="groups"
                                                v-model="registerFormData.group"
                                                prepend-icon="person"
                                                label="Course"
                                                single-line
                                                required
                                                :rules="[rules.required]"
                                            />
                                        </v-flex>
                                        <v-flex xs12 sm6>
                                            <v-text-field
                                                v-model="registerFormData.password"
                                                prepend-icon="lock"
                                                label="Password"
                                                type="password"
                                                required
                                                :rules="[rules.required]"
                                            />
                                        </v-flex>
                                        <v-flex xs12 sm6>
                                            <v-text-field
                                                v-model="registerFormData.confirmPassword"
                                                prepend-icon="lock"
                                                label="Confirm password"
                                                type="password"
                                                required
                                                :rules="[rules.required]"
                                            />
                                        </v-flex>
                                        <v-flex xs12>
                                            <file-drop ref="fileDrop" label="Your picture (we must be able to see your face): *" allowed-types="images" />
                                        </v-flex>
                                        <v-flex xs12>
                                            <vue-recaptcha :sitekey="recaptchaKey" @verify="onCaptchaVerify"></vue-recaptcha>
                                        </v-flex>
                                        <v-flex xs12>
                                            <v-btn color="primary" outline @click="register = false">Back</v-btn>
                                            <v-btn color="primary" @click="onRegister">Register</v-btn>
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
import FileDrop from 'components/FileDrop.vue'

export default {
    data() {
        return {
            register: false,
            loginFormData: {
                username: '',
                password: ''
            },
            registerFormData: {
                firstName: '',
                lastName: '',
                room: '',
                group: '',
                email: '',
                phone: '',
                password: '',
                confirmPassword: '',
                captchaReponse: ''
            },
            groups: [],
            isLoading: false,
            errorMsg: '',
            rules: {
                required(value) {
                    return !!value || 'Required.'
                }
            },
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
            const $this = this

            if (this.isLoading) {
                return
            }

            this.isLoading = true

            var payload = {
                username: this.registerFormData.email,
                firstName: this.registerFormData.firstName,
                lastName: this.registerFormData.lastName,
                room: this.registerFormData.room,
                group: this.registerFormData.group,
                phone: this.registerFormData.phone,
                password: this.registerFormData.password,
                captchaReponse: ''
            }

            Axios.post(Config.endpoint + 'register', payload)
                .then(function (response) {
                    $this.$store.commit('userAuthSuccess', response.data.token)

                    $this.$router.push({ name: 'page-news' })

                    $this.isLoading = false
                })
                .catch(function (error) {
                    console.log('register error', error)

                    $this.isLoading = false
                })
        },

        onCaptchaVerify(respone) {
            this.registerFormData.captchaReponse = respone
        },

        /**
        * Submission of the login form
        * @param Event e the DOM event
        */
        onLogin(e) {
            const $this = this

            if (this.isLoading) {
                return
            }

            this.isLoading = true

            Axios.post(Config.endpoint + 'login', this.loginFormData)
                .then(function (response) {
                    // Store the token
                    $this.$store.commit('userAuthSuccess', response.data.token)

                    $this.$router.push({ name: 'page-news' })

                    $this.isLoading = false
                })
                .catch(function (error) {
                    console.log('login error', error)
                    $this.isLoading = false

                    // error received from the server
                    if (error.response && _.has(error, 'response.data.message')) {
                        $this.errorMsg = error.response.data.message
                    }
                    // no answer from the server, or no error message in the body
                    else {
                        $this.errorMsg = 'An error has occurred, please try again. If the problem persists please contact the student committee'
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
        // this.initCaptcha()
    },
    components: {
        VueRecaptcha,
        'file-drop': FileDrop
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
