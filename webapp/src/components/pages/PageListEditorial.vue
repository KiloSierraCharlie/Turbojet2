<template>
    <v-container fluid class="page-list-editorial pt-0">
        <v-layout row>
            <v-flex xs12>
                <v-dialog v-model="dialogEdit" max-width="500px" fullscreen transition="dialog-bottom-transition">
                    <v-btn v-show="connectedUser ? connectedUser.hasPermissions('permission_edit_announcement') : false" slot="activator" color="primary" dark class="mb-2">{{ buttonLabel }}</v-btn>
                    <v-card tile>
                        <v-toolbar card dark color="primary">
                            <v-spacer></v-spacer>
                            <v-toolbar-title>{{ formTitle }}</v-toolbar-title>
                            <v-spacer></v-spacer>
                            <v-btn icon @click="closeDialogEdit" dark>
                                <v-icon>close</v-icon>
                            </v-btn>
                        </v-toolbar>
                        <v-card-text class="dialog-content">
                            <v-form enctype="multipart/form-data" ref="form" v-model="formIsValid">
                                <v-text-field
                                    v-model="editedPost.title"
                                    label="Title"
                                    persistent-hint
                                    name="title"
                                    v-validate="{required: true}"
                                    :error="errors.has('title')"
                                    :error-messages="errors.collect('title')"
                                    class="mb-1"
                                />
                                <vue-editor v-model="editedPost.content"
                                    useCustomImageHandler
                                    @imageAdded="handleImageAdded"
                                >
                                </vue-editor>
                            </v-form>
                        </v-card-text>
                        <v-card-actions>
                            <v-spacer></v-spacer>
                            <v-btn :disabled="isLoading" outline color="primary" @click="closeDialogEdit">Cancel</v-btn>
                            <v-btn :loading="isLoading" color="primary" @click="savePost">Save</v-btn>
                        </v-card-actions>

                    </v-card>
                </v-dialog>
                <v-dialog v-model="dialogDelete" max-width="500px" persistent>
                    <v-card>
                        <v-card-title class="headline">Confirm deletion ?</v-card-title>
                        <v-card-text>Are you sure you want to delete this post? This action can not be undone</v-card-text>
                        <v-card-actions>
                            <v-spacer></v-spacer>
                            <v-btn :disabled="isLoading" outline color="primary" @click="closeDialogDelete">Cancel</v-btn>
                            <v-btn :loading="isLoading" color="primary" @click="deletePost">Confirm</v-btn>
                        </v-card-actions>
                    </v-card>
                </v-dialog>
                <v-card v-for="(post, index) in posts" :key="post.id" :class="{'mt-4': index !== 0}">
                    <v-card-title
                        :class="'white--text ' + post.color"
                        src="/static/doc-images/cards/docks.jpg"
                    >
                        <span class="headline">{{post.title}}</span>
                        <v-spacer></v-spacer>
                        <v-menu left v-show="connectedUser ? connectedUser.hasPermissions('permission_edit_announcement') : false">
                            <v-btn slot="activator" icon dark>
                                <v-icon>mdi-settings</v-icon>
                            </v-btn>
                            <v-list>
                                <v-list-tile @click="editPost(post)">
                                    <v-list-tile-action><v-icon>mdi-pencil</v-icon></v-list-tile-action>
                                    <v-list-tile-title>Edit</v-list-tile-title>
                                </v-list-tile>
                                <v-list-tile @click="dialogDelete = true; postToDelete = post.id ">
                                    <v-list-tile-action><v-icon>mdi-delete-forever</v-icon></v-list-tile-action>
                                    <v-list-tile-title>Delete</v-list-tile-title>
                                </v-list-tile>
                            </v-list>
                        </v-menu>
                    </v-card-title>
                    <v-card-text>
                        <div>
                            <div class="mb-2 grey--text text--darken-2">
                                <span><v-icon small class="mr-1">mdi-calendar-text</v-icon>{{formatDate(post.date)}}</span>
                                <a class="ml-2" @click="clickUser(post.id_user)"><v-icon small class="mr-1">mdi-account</v-icon>{{post.name}}</a>
                            </div>
                            <div v-html="post.content"></div>
                        </div>
                    </v-card-text>
                </v-card>

            </v-flex xs12>
        </v-layout row>
        <v-layout row class="mt-3" v-if="totalPages > 1">
            <v-flex xs12>
                <div class="text-xs-center">
                    <v-pagination
                        circle :length="totalPages" v-model="currentPage"
                        total-visible="7" @input="onPageChange" />
                </div>
            </v-flex>
        </v-layout>
        <v-snackbar :timeout="0" color="red accent-2" v-model="snackbar">
          {{ errorMessage }}
          <v-btn dark flat @click.native="snackbar = false; errorMessage = ''">Close</v-btn>
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
    name: 'page-list-editorial',
    data() {
        return {
            posts: [],
            currentPage: 1,
            totalPages: 0,
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
            editedPost: {
                id: '',
                title: '',
                content: ''
            },
            postToDelete: '',
            dialogDelete: false,
            editedIndex: -1,
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
        connectedUser() {
            return this.$store.state.connectedUser
        },
        isEdit() {
            return this.editedIndex !== -1
        },
        formTitle () {
            return this.isEdit ? this.$route.meta.labels.formTitleEdit : this.$route.meta.labels.formTitleNew
        },
        buttonLabel () {
            return this.$route.meta.labels.formTitleNew
        },
        totalToDisplay() {
            return this.$route.meta.settings.totalToDisplay
        }
    },
    created() {
        this.fetchData()
    },
    watch: {
        '$route': 'fetchData',
        dialogEdit (val) {
            val || this.closeDialogEdit()
        },
        dialogDelete (val) {
            val || this.closeDialogDelete()
        }
    },
    methods: {
        fetchData() {
            const $this = this

            Axios.get(Config.endpoint + this.$route.meta.api.getAll + '?from='+((this.currentPage-1)*this.totalToDisplay)+'&length='+this.totalToDisplay)
                .then(function (response) {
                    $this.posts = _.map(response.data.posts, function(post) {
                        post.color = $this.randomColor()
                        return post
                    })
                    $this.totalPages = _.toInteger(response.data.totalPages)
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
        clickUser(id) {
            this.$root.$emit('showUser', id)
        },
        onPageChange() {
            this.fetchData()
            window.scrollTo(0, 0)
        },
        randomColor() {
            return _.sample(this.colors)
        },
        formatDate(date) {
            return moment(date).format("dddd, MMMM Do YYYY, h:mm a")
        },
        savePost() {
            const $this = this

            this.$validator.validateAll()
                .then(function(res) {
                    if(res) {
                        var success = function(response) {
                            $this.isLoading = false

                            $this.closeDialogEdit()
                            $this.fetchData()
                        }

                        var error = function(error) {
                            $this.isLoading = false

                            if(_.has(error, 'response.data.message')) {
                                $this.errorMessage = error.response.data.message
                                $this.snackbar = true
                            }
                            else {
                                $this.errorMessage = 'An error occured, please try again'
                                $this.snackbar = true
                            }
                        }

                        // New post / link => POST request
                        if($this.editedIndex === -1) {
                            Axios.post(Config.endpoint + $this.$route.meta.api.post, $this.editedPost)
                                .then(success)
                                .catch(error)
                        }

                        // Edit existing post / link => PUT request
                        else {
                            Axios.post(Config.endpoint + $this.$route.meta.api.edit.replace('{id}', $this.editedPost.id), $this.editedPost)
                                .then(success)
                                .catch(error)
                        }

                        $this.isLoading = true
                    }
                })
        },
        closeDialogEdit() {
            const $this = this

            this.dialogEdit = false
            this.snackbar = false
            this.editedIndex = -1

            setTimeout(() => {
                $this.resetForm()
            }, 300)
        },
        closeDialogDelete () {
            this.dialogDelete = false
            this.snackbar = false
        },
        resetForm() {
            this.$refs.form.reset()
            this.$validator.reset()
            this.editedPost.id = ''
            this.editedPost.title = ''
            this.editedPost.content = ''
        },
        handleImageAdded: function(file, Editor, cursorLocation, resetUploader) {
            console.log('handleImageAdded', file);

            var formData = new FormData();
            formData.append('file', file)

            Axios.post(Config.endpoint + this.$route.meta.api.imageUpload, formData)
            .then((result) => {
                Editor.insertEmbed(cursorLocation, 'image', result.data.file.path);
                resetUploader();
            })
            .catch((err) => {
              console.log(err);
            })
        },
        editPost(post) {
            this.editedIndex = this.posts.indexOf(post)
            this.editedPost.title = post.title
            this.editedPost.content = post.content
            this.editedPost.id = post.id
            this.dialogEdit = true
        },
        deletePost() {
            const $this = this

            Axios.delete(Config.endpoint + this.$route.meta.api.delete.replace('{id}', this.postToDelete))
                .then(function(response) {
                    $this.isLoading = false
                    $this.postToDelete = ''
                    $this.dialogDelete = false
                    $this.snackbar = false
                    $this.fetchData()
                })
                .catch(function(error) {
                    $this.isLoading = false

                    console.log('error', error)

                    if(_.has(error, 'response.data.message')) {
                        $this.errorMessage = error.response.data.message
                        $this.snackbar = true
                    }
                    else {
                        $this.errorMessage = 'An error occured, please try again'
                        $this.snackbar = true
                    }
                })
        }
    },
    components: {
        'vue-editor': VueEditor
    }
}
</script>

<style lang="scss">
    .page-list-editorial {
        .v-card img {
            max-width: 100%;

        }
    }
</style>
