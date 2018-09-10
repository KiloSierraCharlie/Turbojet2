<template>
    <v-container fluid class="page-manager">
        <v-layout row>
            <v-flex xs12>
                <v-dialog v-model="dialogEdit" max-width="500px" fullscreen transition="dialog-bottom-transition">
                    <v-btn v-show="connectedUser ? connectedUser.hasPermissions($route.meta.settings.permission) : false" slot="activator" color="primary" dark class="mb-2">New Page</v-btn>
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
                                <!-- <v-text-field
                                    v-model="editedPage.menu_icon"
                                    label="Icon"
                                    persistent-hint
                                    required
                                    :rules="[rules.required]"
                                    class="mb-1"
                                /> -->
                                <v-text-field
                                    v-model="editedPage.title"
                                    label="Title"
                                    name="title"
                                    persistent-hint
                                    required
                                    v-validate="{required: true}"
                                    :error="errors.has('title')"
                                    :error-messages="errors.collect('title')"
                                    class="mb-1"
                                />
                                <vue-editor v-model="editedPage.content"
                                    useCustomImageHandler
                                    @imageAdded="handleImageAdded"
                                >
                                </vue-editor>
                            </v-form>
                        </v-card-text>
                        <v-card-actions>
                            <v-spacer></v-spacer>
                            <v-btn :disabled="isLoading" outline color="primary" @click="closeDialogEdit">Cancel</v-btn>
                            <v-btn :loading="isLoading" color="primary" @click="savePage">Save</v-btn>
                        </v-card-actions>
                    </v-card>
                </v-dialog>
                <v-dialog v-model="dialogDelete" max-width="500px" persistent>
                    <v-card>
                        <v-card-title class="headline">Confirm deletion ?</v-card-title>
                        <v-card-text>Are you sure you want to delete this page? This action can not be undone</v-card-text>
                        <v-card-actions>
                            <v-spacer></v-spacer>
                            <v-btn :disabled="isLoading" outline color="primary" @click="closeDialogDelete">Cancel</v-btn>
                            <v-btn :loading="isLoading" color="primary" @click="deletePage">Confirm</v-btn>
                        </v-card-actions>
                    </v-card>
                </v-dialog>

                <v-data-table
                    :headers="computedHeaders"
                    :items="pages"
                    hide-actions
                    class="elevation-1"
                >
                    <template slot="items" slot-scope="data">
                        <td><v-icon>mdi-file-document-box</v-icon></td>
                        <td><router-link :to="'/pages/'+data.item.type+'/'+data.item.id">{{ data.item.title }}</router-link></td>
                        <td class="layout px-0" v-if="connectedUser ? connectedUser.hasPermissions($route.meta.settings.permission) : false">
                            <v-btn icon class="mx-0" @click="editPage(data.item)">
                                <v-icon color="teal">mdi-pencil</v-icon>
                            </v-btn>
                            <v-btn icon class="mx-0" @click="dialogDelete = true; pageToDelete = data.item.id ">
                                <v-icon color="pink">mdi-delete-forever</v-icon>
                            </v-btn>
                        </td>
                    </template>
                    <template slot="no-data">
                        <v-alert :value="true" type="info">
                            Sorry, nothing to display here :(
                        </v-alert>
                    </template>
                  </v-data-table>
            </v-flex xs12>
        </v-layout row>
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
import Config from 'src/Config.__ENV__.js'
import FileDrop from 'components/FileDrop.vue'
import { VueEditor, Quill } from 'vue2-editor'

export default {
    name: 'page-manager',
    props: ['section'],
    data() {
        return {
            dialogEdit: false,
            formIsValid: false,
            snackbar: false,
            isLoading: false,
            errorMessage: '',
            editedPage: {
                id: '',
                // icon: '',
                title: '',
                content: ''
            },
            pageToDelete: '',
            dialogDelete: false,
            editedIndex: -1,
            files: [],
            showSettings: false,
            pages: [],
            headers: [
                { text: 'Icon', value: 'icon', sortable: false},
                { text: 'Name', value: 'title' }
            ],
            snackbar: false,
            errorMessage: ''
        }
    },
    computed: {
        computedHeaders() {
            if(this.connectedUser && this.connectedUser.hasPermissions(this.$route.meta.settings.permission)) {
                return _.union(this.headers, [
                    { text: 'Actions', value: 'actions', sortable: false }
                ])
            }
            else {
                return this.headers
            }
        },
        connectedUser() {
            return this.$store.state.connectedUser
        },
        formTitle () {
            return this.editedIndex === -1 ? 'New Page' : 'Edit Page'
        }
    },
    watch: {
        '$route': 'fetchData'
    },
    methods: {
        fetchData() {
            const $this = this

            Axios.get(Config.endpoint + this.$route.meta.api.getAll)
                .then(function (response) {
                    $this.pages = response.data.posts
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
        onPageChange() {
            this.fetchData()
            window.scrollTo(0, 0)
        },
        formatDate(date) {
            return moment(date).format("dddd, MMMM Do YYYY, h:mm a")
        },
        savePage() {
            var $this = this

            this.$validator.validateAll()
                .then(function(res) {
                    if(res) {
                        var success = function(response) {
                            $this.isLoading = false

                            $this.closeDialogEdit()
                            $this.fetchData()
                            $this.$store.dispatch('fetchDynamicMenuSections')
                        }

                        var error = function(error) {
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
                        }

                        // New pages / link => POST request
                        if($this.editedIndex === -1) {
                            Axios.post(Config.endpoint + $this.$route.meta.api.post, $this.editedPage)
                                .then(success)
                                .catch(error)
                        }

                        // Edit existing page / link => PUT request
                        else {
                            Axios.post(Config.endpoint + $this.$route.meta.api.edit.replace('{id}', $this.editedPage.id), $this.editedPage)
                                .then(success)
                                .catch(error)
                        }

                        $this.isLoading = true
                    }
                })
        },
        closeDialogEdit() {
            var $this = this

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
            this.editedPage.id = ''
            // this.editedPage.menu_icon = ''
            this.editedPage.title = ''
            this.editedPage.content = ''
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
        editPage(page) {
            this.editedIndex = this.pages.indexOf(page)
            // this.editedPage.menu_icon = page.menu_icon
            this.editedPage.title = page.title
            this.editedPage.content = page.content
            this.editedPage.id = page.id
            this.dialogEdit = true
        },
        deletePage() {
            var $this = this

            Axios.delete(Config.endpoint + this.$route.meta.api.delete.replace('{id}', this.pageToDelete))
                .then(function(response) {
                    $this.isLoading = false
                    $this.pageToDelete = ''
                    $this.dialogDelete = false
                    $this.snackbar = false
                    $this.fetchData()
                    $this.$store.dispatch('fetchDynamicMenuSections')
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
    created() {
        this.fetchData()
    },
    components: {
        'file-drop': FileDrop,
        'vue-editor': VueEditor
    }
}
</script>

<style scoped lang="scss"></style>
