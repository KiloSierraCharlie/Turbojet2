<template>
    <v-container fluid class="page-ftebay">
        <v-layout row>
            <v-flex xs12>
                <v-dialog v-model="dialogEdit" max-width="500px" fullscreen transition="dialog-bottom-transition" scrollable>
                    <v-btn slot="activator" color="primary" dark class="mb-2">New FTEbay offer</v-btn>
                    <v-card tile>
                        <v-form enctype="multipart/form-data" ref="form" v-model="formIsValid">
                            <v-toolbar card dark color="primary">
                                <v-spacer></v-spacer>
                                <v-toolbar-title>{{ formTitle }}</v-toolbar-title>
                                <v-spacer></v-spacer>
                                <v-btn icon @click="closeDialogEdit" dark>
                                    <v-icon>close</v-icon>
                                </v-btn>
                            </v-toolbar>
                            <v-card-text class="dialog-content">
                                <v-text-field
                                    v-model="editedOffer.title"
                                    label="Title"
                                    persistent-hint
                                    required
                                    :rules="[rules.required]"
                                    class="mb-1"
                                />
                                <vue-editor v-model="editedOffer.content"
                                    useCustomImageHandler
                                    @imageAdded="handleImageAdded"
                                >
                                </vue-editor>
                            </v-card-text>
                            <v-card-actions>
                                <v-spacer></v-spacer>
                                <v-btn :disabled="isLoading" outline color="primary" @click="closeDialogEdit">Cancel</v-btn>
                                <v-btn :loading="isLoading" color="primary" @click="saveOffer">Save</v-btn>
                            </v-card-actions>
                        </v-form>
                    </v-card>
                </v-dialog>
                <v-dialog v-model="dialogDelete" max-width="500px" persistent>
                    <v-card>
                        <v-card-title class="headline">Confirm deletion ?</v-card-title>
                        <v-card-text>Are you sure you want to delete this offer? This action can not be undone</v-card-text>
                        <v-card-actions>
                            <v-spacer></v-spacer>
                            <v-btn :disabled="isLoading" outline color="primary" @click="closeDialogDelete">Cancel</v-btn>
                            <v-btn :loading="isLoading" color="primary" @click="deleteOffer">Confirm</v-btn>
                        </v-card-actions>
                    </v-card>
                </v-dialog>
                <v-card v-for="(post, index) in posts" :key="post.id" :class="{'mt-4': index !== 0}">
                    <v-card-title
                        :class="'white--text ' + randomColor()"
                        src="/static/doc-images/cards/docks.jpg"
                    >
                        <span class="headline">{{post.title}}</span>
                        <v-spacer></v-spacer>
                        <v-menu left>
                            <v-btn slot="activator" icon dark>
                                <v-icon>mdi-settings</v-icon>
                            </v-btn>
                            <v-list>
                                <v-list-tile @click="editOffer(post)">
                                    <v-list-tile-action><v-icon>mdi-pencil</v-icon></v-list-tile-action>
                                    <v-list-tile-title>Edit</v-list-tile-title>
                                </v-list-tile>
                                <v-list-tile @click="dialogDelete = true; offerToDelete = post.id ">
                                    <v-list-tile-action><v-icon>mdi-delete</v-icon></v-list-tile-action>
                                    <v-list-tile-title>Delete</v-list-tile-title>
                                </v-list-tile>
                            </v-list>
                        </v-menu>
                    </v-card-title>
                    <v-card-text>
                        <div>
                            <div class="mb-2 grey--text text--darken-2">
                                <span><v-icon small class="mr-1">mdi-calendar-text</v-icon>{{formatDate(post.date)}}</span>
                                <span class="ml-2"><router-link :to="'user/'+post.id_user"><v-icon small class="mr-1">mdi-account</v-icon>{{post.name}}</router-link></span>
                            </div>
                            <div v-html="post.content"></div>
                        </div>
                    </v-card-text>
                    <!-- <v-card-actions>

                    </v-card-actions> -->
                </v-card>

            </v-flex xs12>
        </v-layout row>
        <v-snackbar :timeout="0" color="red accent-2" v-model="snackbar">
          {{ errorMessage }}
          <v-btn dark flat @click.native="snackbar = false">Close</v-btn>
        </v-snackbar>
    </v-container>
</template>

<script>
import Axios from 'axios'
import moment from 'moment'
import _ from 'lodash'
// import { VueEditor } from 'vue2-editor'
import { VueEditor, Quill } from 'vue2-editor'
// import ImageResize from 'quill-image-resize-module'
import Config from 'src/Config.__ENV__.js'

// Quill.register('modules/imageResize', ImageResize)

export default {
    name: 'page-ftebay',
    data() {
        return {
            posts: [],
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
            ],
            dialogEdit: false,
            formIsValid: false,
            snackbar: false,
            isLoading: false,
            errorMessage: '',
            editedOffer: {
                id: '',
                title: '',
                content: ''
            },
            offerToDelete: '',
            dialogDelete: false,
            editedIndex: -1,
            rules: {
                required(value) {
                    return !!value || 'Required.'
                }
            },
            files: [],
            showSettings: false,
            // editorModules: [
            //     { alias: 'imageResize', module: ImageResize }
            // ],
            // editorOptions: {
            //     modules: {
            //         imageResize: {}
            //     }
            // }
        }
    },
    computed: {
        formTitle () {
            return this.editedIndex === -1 ? 'New FTEbay offer' : 'Edit FTEbay offer'
        }
    },
    created() {
        this.fetchPostsData()
    },
    watch: {
        dialogEdit (val) {
            val || this.closeDialogEdit()
        },
        dialogDelete (val) {
            val || this.closeDialogDelete()
        }
    },
    methods: {
        fetchPostsData() {
            var that = this

            Axios.get(Config.endpoint + 'ftebay-posts')
                .then(function (response) {
                    that.posts = response.data
                    // that.totalPages = _.toInteger(response.data.totalPages)
                    console.log('fetch news data success', response.data)
                })
                .catch(function (error) {
                    if(_.has(error, 'message')) {
                        self.errorMessage = error.message
                        self.snackbar = true
                    }
                    else {
                        self.errorMessage = 'An error occured, please try again'
                        self.snackbar = true
                    }
                });
        },
        randomColor() {
            return _.sample(this.colors)
        },
        formatDate(date) {
            return moment(date).format("dddd, MMMM Do YYYY, h:mm a")
        },
        saveOffer() {
            var self = this

            if (this.$refs.form.validate()) {

                var success = function(response) {
                    self.isLoading = false

                    self.closeDialogEdit()
                    self.fetchPostsData()
                }

                var error = function(error) {
                    self.isLoading = false

                    console.log('error', error)

                    if(_.has(error, 'message')) {
                        self.errorMessage = error.message
                        self.snackbar = true
                    }
                    else {
                        self.errorMessage = 'An error occured, please try again'
                        self.snackbar = true
                    }
                }

                // New offer / link => POST request
                if(this.editedIndex === -1) {
                    Axios.post(Config.endpoint + 'ftebay-posts', this.editedOffer)
                        .then(success)
                        .catch(error)
                }

                // Edit existing offer / link => PUT request
                else {
                    Axios.post(Config.endpoint + 'ftebay-posts/'+this.editedOffer.id+'/edit', this.editedOffer)
                        .then(success)
                        .catch(error)
                }

                this.isLoading = true
            }
        },
        closeDialogEdit() {
            var self = this

            this.dialogEdit = false
            this.snackbar = false
            this.editedIndex = -1

            setTimeout(() => {
                self.resetForm()
            }, 300)
        },
        closeDialogDelete () {
            this.dialogDelete = false
            this.snackbar = false
        },
        resetForm() {
            this.$refs.form.reset()
            this.editedOffer.id = ''
            this.editedOffer.title = ''
            this.editedOffer.content = ''
        },
        handleImageAdded: function(file, Editor, cursorLocation, resetUploader) {
            console.log('handleImageAdded', file);

            var formData = new FormData();
            formData.append('file', file)

            Axios.post(Config.endpoint + 'ftebay-posts/image', formData)
            .then((result) => {
                Editor.insertEmbed(cursorLocation, 'image', result.data.file.path);
                resetUploader();
            })
            .catch((err) => {
              console.log(err);
            })
        },
        editOffer(post) {
            this.editedIndex = this.posts.indexOf(post)
            this.editedOffer.title = post.title
            this.editedOffer.content = post.content
            this.editedOffer.id = post.id
            this.dialogEdit = true
        },
        deleteOffer() {
            var self = this

            Axios.delete(Config.endpoint + 'ftebay-posts/'+this.offerToDelete)
                .then(function(response) {
                    self.isLoading = false
                    self.offerToDelete = ''
                    self.dialogDelete = false
                    self.snackbar = false
                    self.fetchPostsData()
                })
                .catch(function(error) {
                    self.isLoading = false

                    console.log('error', error)

                    if(_.has(error, 'message')) {
                        self.errorMessage = error.message
                        self.snackbar = true
                    }
                    else {
                        self.errorMessage = 'An error occured, please try again'
                        self.snackbar = true
                    }
                })
        }
    },
    components: {
        'vue-editor': VueEditor
    }
}
</script>

<style scoped lang="scss">
    .page-ftebay {
    }


</style>
