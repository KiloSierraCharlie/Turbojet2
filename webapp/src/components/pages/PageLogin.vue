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
                    <v-form data-vv-scope="login-form">
                        <v-text-field
                            v-model="loginFormData.username"
                            prepend-icon="person"
                            label="Email"
                            type="email"
                            name="loginEmail"
                            v-validate="{required: true, email: true}"
                            :error="errors.has('login-form.loginEmail')"
                            :error-messages="errors.collect('login-form.loginEmail')"
                        />
                        <v-text-field
                            v-model="loginFormData.password"
                            prepend-icon="lock"
                            label="Password"
                            type="password"
                            name="loginPassword"
                            v-validate="{required: true}"
                            :error="errors.has('login-form.loginPassword')"
                            :error-messages="errors.collect('login-form.loginPassword')"
                        />
                        <div class="text-xs-center">
                            <v-btn outline color="primary" @click="goRegister">Register</v-btn>
                            <v-btn color="primary" @click="onLogin">Login</v-btn>
                        </div>
                    </v-form>
                </div>
            </v-flex>
            <v-flex sm6 md8 lg9 :style="'background: url(/public/login-backgrounds/'+backgroundImage+') center center / cover no-repeat'" />
        </v-layout>
        <v-dialog v-model="register" fullscreen hide-overlay transition="dialog-bottom-transition">
            <v-card tile>
                <v-form enctype="multipart/form-data" ref="form" v-model="formIsValid" data-vv-scope="register-form">
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
                                                    name="firstName"
                                                    type="text"
                                                    v-validate="{required: true}"
                                                    :error="errors.has('register-form.firstName')"
                                                    :error-messages="errors.collect('register-form.firstName')"
                                                />
                                            </v-flex>
                                            <v-flex xs12 sm6>
                                                <v-text-field
                                                    v-model="registerFormData.lastName"
                                                    prepend-icon="person"
                                                    label="Lastname"
                                                    name="lastName"
                                                    type="text"
                                                    required
                                                    v-validate="{required: true}"
                                                    :error="errors.has('register-form.lastName')"
                                                    :error-messages="errors.collect('register-form.lastName')"
                                                />
                                            </v-flex>
                                            <v-flex xs12 sm6>
                                                <v-text-field
                                                    v-model="registerFormData.email"
                                                    prepend-icon="person"
                                                    label="Email"
                                                    name="email"
                                                    type="email"
                                                    v-validate="{required: true, email:true}"
                                                    :error="errors.has('register-form.email')"
                                                    :error-messages="errors.collect('register-form.email')"
                                                />
                                            </v-flex>
                                            <v-flex xs12 sm6>
                                                <v-text-field
                                                    v-model="registerFormData.phone"
                                                    prepend-icon="person"
                                                    label="Phone"
                                                    name="phone"
                                                    type="text"
                                                    v-validate="{required: true}"
                                                    :error="errors.has('register-form.phone')"
                                                    :error-messages="errors.collect('register-form.phone')"
                                                />
                                            </v-flex>
                                            <v-flex xs12 sm6>
                                                <v-text-field
                                                    v-model="registerFormData.room"
                                                    prepend-icon="person"
                                                    label="Room"
                                                    name="room"
                                                    type="text"
                                                    v-validate="{required: true}"
                                                    :error="errors.has('register-form.room')"
                                                    :error-messages="errors.collect('register-form.room')"
                                                />
                                            </v-flex>
                                            <v-flex xs12 sm6>
                                                <v-select
                                                    :items="groups"
                                                    v-model="registerFormData.group"
                                                    prepend-icon="person"
                                                    label="Course"
                                                    name="course"
                                                    single-line
                                                    v-validate="{required: true}"
                                                    :error="errors.has('register-form.course')"
                                                    :error-messages="errors.collect('register-form.course')"
                                                />
                                            </v-flex>
                                            <v-flex xs12 sm6>
                                                <v-text-field
                                                    v-model="registerFormData.password"
                                                    prepend-icon="lock"
                                                    label="Password"
                                                    name="password"
                                                    ref="password"
                                                    type="password"
                                                    v-validate="{min:6, required: true}"
                                                    :error="errors.has('register-form.password')"
                                                    :error-messages="errors.collect('register-form.password')"
                                                />
                                            </v-flex>
                                            <v-flex xs12 sm6>
                                                <v-text-field
                                                    v-model="registerFormData.confirmPassword"
                                                    prepend-icon="lock"
                                                    label="Confirm password"
                                                    name="passwordConfirm"
                                                    type="password"
                                                    v-validate="{min:6, required: true, confirmed: 'password'}"
                                                    :error="errors.has('register-form.passwordConfirm')"
                                                    :error-messages="errors.collect('register-form.passwordConfirm')"
                                                />
                                            </v-flex>
                                            <v-flex xs12>
                                                <file-drop ref="fileDrop" label="Your picture (we must be able to see your face): *" allowed-types="images" />
                                            </v-flex>
                                            <v-flex xs12>
                                                <vue-recaptcha ref="captcha" :sitekey="recaptchaKey" @verify="onCaptchaVerify"></vue-recaptcha>
                                            </v-flex>
                                            <v-flex xs12>
                                                <v-btn color="primary" outline @click="closeRegister">Back</v-btn>
                                                <v-btn color="primary" :loading="isLoading" @click="onRegister">Register</v-btn>
                                            </v-flex>
                                        </v-layout>
                                    </v-container>
                                </v-flex>
                            </v-layout>
                        </v-container>
                    </v-card-text>
                </v-form>
            </v-card>
        </v-dialog>
        <v-snackbar :timeout="0" color="red accent-2" v-model="errorSnackbar">
          {{ errorMessage }}
          <v-btn dark flat @click.native="errorSnackbar = false; errorMessage= ''">Close</v-btn>
        </v-snackbar>
        <v-snackbar :timeout="0" color="success" v-model="confirmSnackbar">
          {{ confirmMessage }}
          <v-btn dark flat @click.native="confirmSnackbar = false; confirmMessage= ''">Close</v-btn>
        </v-snackbar>
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
            formIsValid: false,
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
            errorSnackbar: false,
            errorMessage: '',
            confirmSnackbar: false,
            confirmMessage: '',

        }
    },
    computed: {
        backgroundImage() {
            return _.sample([
                'stock-photo-202413751.jpg',
                'stock-photo-241687465.jpg',
                '32545605703_da157f563c_k.jpg',
                '17834084238_cf249eea2f_k.jpg',
                '6852968917_45a0e3718c_o.jpg',
                '9420551811_3f46ab307e_k.jpg',
                '6635883637_19678f38b4_o.jpg',
                '4553065262_3a71c1825b_o.jpg',
                '16400282722_86d1dee4e6_o.jpg',
                '35332726055_c8fd88581a_o.jpg'
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

        closeRegister() {
            const $this = this

            this.register = false
            this.errorSnackbar = false

            setTimeout(() => {
                $this.resetForm()
            }, 300)
        },

        resetForm() {
            this.$refs.form.reset()
            this.$validator.reset()
            this.$refs.captcha.reset()
            this.registerFormData.firstName = ''
            this.registerFormData.lastName = ''
            this.registerFormData.room = ''
            this.registerFormData.group = ''
            this.registerFormData.email = ''
            this.registerFormData.phone = ''
            this.registerFormData.password = ''
            this.registerFormData.confirmPassword = ''
            this.registerFormData.captchaReponse = ''
        },

        onRegister(e) {
            const $this = this

            if (this.isLoading) {
                return
            }

            console.log('this.$validator.', this.$validator)

            this.$validator.validateAll('register-form')
                .then(function(res) {
                    if(res && $this.$refs.fileDrop.isValid() && $this.registerFormData.captchaReponse) {
                        $this.isLoading = true

                        var payload = new FormData()
                        payload.append('file', $this.$refs.fileDrop.file)
                        payload.append('username', $this.registerFormData.email)
                        payload.append('firstName', $this.registerFormData.firstName)
                        payload.append('lastName', $this.registerFormData.lastName)
                        payload.append('room', $this.registerFormData.room)
                        payload.append('group', $this.registerFormData.group)
                        payload.append('phone', $this.registerFormData.phone)
                        payload.append('password', $this.registerFormData.password)
                        payload.append('captchaReponse', $this.registerFormData.captchaReponse)

                        Axios.post(Config.endpoint + 'register', payload)
                            .then(function (response) {
                                $this.register = false
                                $this.isLoading = false

                                $this.confirmSnackbar = true
                                $this.confirmMessage = 'Account created, you will receive an email once it\'s been validated by an administrator'

                                setTimeout(() => {
                                    $this.resetForm()
                                }, 300)
                            })
                            .catch(function(error) {
                                $this.displayError(error)
                                $this.$refs.captcha.reset()
                                $this.registerFormData.captchaReponse = ''
                            })
                    }
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

            this.$validator.validateAll('login-form')
                .then(function(res) {
                    console.log('res', res)
                    if(res) {
                        $this.isLoading = true

                        Axios.post(Config.endpoint + 'login', $this.loginFormData)
                            .then(function (response) {
                                // Store the token
                                $this.$store.dispatch('userAuthSuccess', response.data.token)

                                $this.$router.push({ name: 'page-news' })

                                $this.isLoading = false
                            })
                            .catch($this.displayError)
                    }
                })
        },

        getPicklistData() {
            const $this = this

            Axios.get(Config.endpoint + 'picklists/groups')
                .then(function (response) {
                    $this.groups = response.data
                })
                .catch(function (error) {
                    $this.isLoading = false

                    console.log('error', error)

                    if(_.has(error, 'response.data.message')) {
                        $this.errorMessage = error.response.data.message
                        $this.errorSnackbar = true
                    }
                    else {
                        $this.errorMessage = 'An error occured, please try again'
                        $this.errorSnackbar = true
                    }
                });
        },

        displayError(error) {
            console.log('login error', error)
            this.isLoading = false

            // error received from the server
            if (error.response && _.has(error, 'response.data.message')) {
                this.errorMessage = error.response.data.message
                this.errorSnackbar = true
            }
            // no answer from the server, or no error message in the body
            else {
                this.errorMessage = 'An error has occurred, please try again. If the problem persists please contact the student committee'
                this.errorSnackbar = true
            }
        }
    },
    created() {
        this.getPicklistData()
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
