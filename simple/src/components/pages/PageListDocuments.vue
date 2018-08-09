<template>
    <v-container fluid class="page-list-documents">
        <v-layout row>
            <v-flex xs12>
                <v-dialog v-model="dialogEdit" max-width="500px" persistent>
                    <v-btn slot="activator" color="primary" dark class="mb-2">New Document</v-btn>
                    <v-card>
                            <v-card-title>
                                <span class="headline">{{ formTitle }}</span>
                            </v-card-title>
                            <v-card-text>
                                <v-radio-group v-if="editedIndex === -1" v-model="docType">
                                    <v-radio label="Document" value="document"></v-radio>
                                    <v-radio label="Link" value="link"></v-radio>
                                </v-radio-group>
                                <v-form v-show="docType === 'document'" enctype="multipart/form-data" ref="form" v-model="formIsValid" data-vv-scope="document-form">
                                    <v-text-field
                                        v-model="editedDocument.name"
                                        label="Document name"
                                        hint="The document will appear with that name"
                                        persistent-hint
                                        name="documentName"
                                        v-validate="{required: true}"
                                        :error="errors.has('document-form.documentName')"
                                        :error-messages="errors.collect('document-form.documentName')"
                                    />

                                    <file-drop ref="fileDrop" label="Document: *"/>
                                </v-form>
                                <v-form v-show="docType === 'link'" enctype="multipart/form-data" ref="form" v-model="formIsValid" data-vv-scope="link-form">
                                    <v-text-field
                                        v-model="editedDocument.name"
                                        label="Link name"
                                        hint="The link will appear with that name"
                                        persistent-hint
                                        name="linkName"
                                        v-validate="{required: true}"
                                        :error="errors.has('link-form.linkName')"
                                        :error-messages="errors.collect('link-form.linkName')"
                                    />
                                    <v-text-field
                                        v-model="editedDocument.path"
                                        label="Link URL"
                                        hint="E.g.: http://www.google.com"
                                        persistent-hint
                                        name="path"
                                        v-validate="{required: true}"
                                        :error="errors.has('link-form.path')"
                                        :error-messages="errors.collect('link-form.path')"
                                    />
                                </v-form>
                            </v-card-text>
                            <v-card-actions>
                                <v-spacer></v-spacer>
                                <v-btn :disabled="isLoading" outline color="primary" @click="closeDialogEdit">Cancel</v-btn>
                                <v-btn :loading="isLoading" color="primary" @click="save">Save</v-btn>
                            </v-card-actions>
                        </v-form>
                    </v-card>
                </v-dialog>
                <v-dialog v-model="dialogDelete" max-width="500px" persistent>
                    <v-card>
                        <v-card-title class="headline">Confirm deletion ?</v-card-title>
                        <v-card-text>Are you sure you want to delete this item? This action can not be undone</v-card-text>
                        <v-card-actions>
                            <v-spacer></v-spacer>
                            <v-btn :disabled="isLoading" outline color="primary" @click="closeDialogDelete">Cancel</v-btn>
                            <v-btn :loading="isLoading" color="primary" @click="deleteItem">Confirm</v-btn>
                        </v-card-actions>
                    </v-card>
                </v-dialog>
                <v-data-table
                    :headers="headers"
                    :items="documents"
                    hide-actions
                    class="elevation-1"
                >
                    <template slot="items" slot-scope="data">
                        <td>
                            <v-tooltip right>
                                <v-icon slot="activator" :color="getType(data.item).color">{{getType(data.item).icon}}</v-icon>
                                <span>{{getType(data.item).type}}</span>
                            </v-tooltip>

                        </td>
                        <td><a :href="getDocumentLink(data.item)" target="_blank">{{ data.item.name }}</a><v-icon class="ml-1" color="indigo" v-if="isNew(data.item.date_modified)">mdi-new-box</v-icon></td>
                        <td>{{ formatDate(data.item.date_modified) }}</td>
                        <td class="justify-center layout px-0">
                            <v-btn icon class="mx-0" @click="editItem(data.item)">
                                <v-icon color="teal">mdi-pencil</v-icon>
                            </v-btn>
                            <v-btn icon class="mx-0" @click="dialogDelete = true; documentToDelete = data.item.id ">
                                <v-icon color="pink">mdi-delete-forever</v-icon>
                            </v-btn>
                        </td>
                    </template>
                  </v-data-table>
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
import Config from 'src/Config.__ENV__.js'
import FileDrop from 'components/FileDrop.vue'

export default {
    name: 'page-list-documents',
    props: ['collectionSlug'],
    data() {
        return {
            dialogEdit: false,
            dialogDelete: false,
            isLoading: false,
            docType: 'document', // document or link
            editedIndex: -1,
            formIsValid: false,
            documentToDelete: '',
            editedDocument: {
                id: '',
                name: '',
                path: ''
            },
            documents: [],
            headers: [
                { text: 'Type', value: 'type', sortable: false },
                { text: 'Name', value: 'names' }, // TODO nug on sort
                // { text: 'Type', value: 'type' },
                // { text: 'Size', value: 'size' },
                { text: 'Last Updated', value: 'modifiedAt' },
                { text: 'Actions', value: 'name', sortable: false }
            ],
            snackbar: false,
            errorMessage: ''
        }
    },
    computed: {
        formTitle () {
            var title = this.editedIndex === -1 ? 'New' : 'Edit'
            title += this.docType === 'document' ? ' Document' : ' Link'

            return title
        }
    },
    watch: {
        '$route': 'fetchDocumentsData',
        dialogEdit (val) {
            val || this.closeDialogEdit()
        },
        dialogDelete (val) {
            val || this.closeDialogDelete()
        }
    },
    methods: {
        fetchDocumentsData() {
            var that = this

            // this.$store.dispatch('fetchNewsData')
            Axios.get(Config.endpoint + 'documents/'+this.collectionSlug)
                .then(function (response) {
                    console.log('fetch documents data success', response.data)
                    that.documents = response.data

                })
                .catch(function (error) {
                    // TODO manage error
                    console.log(error);
                });
        },

        formatDate(date) {
            return moment(date).format("MMMM Do YYYY, h:mm a")
        },

        getDocumentLink(documentItem) {
            if(documentItem.type === 'document') {
                return 'http://api.turbojet.local/media/documents/'+documentItem.path
            }
            else if(documentItem.type === 'link') {
                return documentItem.path
            }
        },

        editItem (item) {
            this.docType = item.type
            this.editedIndex = this.documents.indexOf(item)
            this.editedDocument.name = item.name
            this.editedDocument.path = item.path || ''
            this.editedDocument.id = item.id
            this.dialogEdit = true
        },

        deleteItem () {
            var self = this

            Axios.delete(Config.endpoint + 'documents/'+this.documentToDelete)
                .then(function(response) {
                    self.isLoading = false
                    self.documentToDelete = ''
                    self.dialogDelete = false
                    self.snackbar = false
                    self.fetchDocumentsData()
                })
                .catch(function(error) {
                    self.isLoading = false

                    console.log('error', error)

                    if(_.has(error, 'response.data.message')) {
                        self.errorMessage = error.response.data.message
                        self.snackbar = true
                    }
                    else {
                        self.errorMessage = 'An error occured, please try again'
                        self.snackbar = true
                    }
                })
        },

        closeDialogEdit () {
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

        save () {
            const $this = this

            this.$validator.validateAll(this.docType === 'document' ? 'document-form' : 'link-form')
                .then(function(res) {
                    if(res) {
                        var payload = new FormData();
                        payload.append('name', $this.editedDocument.name)

                        // console.log('this.$refs.fileDrop.isValid()', this.$refs.fileDrop.isValid())

                        // TODO doc error red field if empty
                        if($this.docType === 'document' && $this.$refs.fileDrop.isValid()) {
                            payload.append('file', $this.$refs.fileDrop.file)
                        }
                        else if($this.docType === 'link') {
                            payload.append('link', $this.editedDocument.path)
                        } else {
                            return
                        }

                        $this.isLoading = true

                        var success = function(response) {
                            $this.isLoading = false

                            $this.closeDialogEdit()
                            $this.fetchDocumentsData()
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

                        // New document / link => POST request
                        if($this.editedIndex === -1) {
                            Axios.post(Config.endpoint + 'documents/'+$this.collectionSlug, payload)
                                .then(success)
                                .catch(error)
                        }

                        // Edit existing document / link => PUT request
                        else {
                            Axios.post(Config.endpoint + 'documents/'+$this.editedDocument.id+'/edit', payload)
                                .then(success)
                                .catch(error)
                        }
                    }
                })

        },

        getType(documentItem) {

            if(documentItem.type === 'link') {
                return {type: 'External link', icon: 'mdi-link-variant', color: 'black'}
            }
            else if(documentItem.type === 'document') {

                var arr = documentItem.path.toLowerCase().split('.')

                switch(arr[arr.length-1]) {
                    case 'jpg':
                    case 'jpeg':
                    case 'gif':
                    case 'png':
                        return {type: 'Image', icon: 'mdi-file-image', color: 'green lighten-1'}
                        break

                    case 'pdf':
                        return {type: 'Pdf', icon: 'mdi-file-pdf', color: 'red darken-3'}
                        break

                    case 'zip':
                        return {type: 'Archive', icon: 'mdi-zip-box', color: 'blue-grey'}
                        break

                    case 'xls':
                    case 'xlsx':
                        return {type: 'Excel', icon: 'mdi-file-excel', color: 'bgreen darken-3'}
                        break

                    case 'doc':
                    case 'docx':
                        return {type: 'Word', icon: 'mdi-file-word', color: 'blue darken-3'}
                        break

                    case 'ppt':
                    case 'pptx':
                    case 'ppsx':
                        return {type: 'Powerpoint', icon: 'mdi-file-powerpoint', color: 'deep-orange darken-1'}
                        break

                    default:
                        return {type: 'not recognized', icon: 'mdi-file-question', color: 'black'}
                }
            }
        },

        isNew(date) {
            return moment().diff(moment(date), 'days') <= 7
        },

        resetForm() {
            // TODO beeing able to edit the collection
            this.$refs.form.reset()
            this.$validator.reset()
            this.docType = 'document'
            this.editedDocument.id = ''
            this.editedDocument.name = ''
            this.editedDocument.path = ''

        }
    },
    created() {
        this.fetchDocumentsData()
    },
    components: {'file-drop': FileDrop}
}
</script>

<style scoped lang="scss">
    .page-list-documents {

    }
</style>
