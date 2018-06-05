<template>
    <v-container fluid class="page-list-documents">
        <v-layout row>
            <v-flex xs12>
                <v-dialog v-model="dialog" max-width="500px">
                    <v-btn slot="activator" color="primary" dark class="mb-2">New Document</v-btn>
                    <v-card>
                        <v-form enctype="multipart/form-data" ref="form" v-model="formIsValid">
                            <v-card-title>
                                <span class="headline">{{ formTitle }}</span>
                            </v-card-title>
                            <v-card-text>
                                <!-- <v-switch label="Use a link" v-model="linkType" /> -->
                                <v-radio-group v-model="docType">
                                    <v-radio label="Document" value="doc"></v-radio>
                                    <v-radio label="Link" value="link"></v-radio>
                                </v-radio-group>
                                <div v-if="docType === 'doc'">
                                    <v-text-field
                                        v-model="editedDocument.name"
                                        label="Document name"
                                        hint="The document will appear with that name"
                                        persistent-hint
                                        required
                                        :rules="[rules.required]"
                                    />

                                    <file-drop ref="fileDrop" label="Document: *"/>
                                </div>
                                <div v-if="docType === 'link'">
                                    <v-text-field
                                        v-model="editedDocument.name"
                                        label="Link name"
                                        hint="The link will appear with that name"
                                        persistent-hint
                                        required
                                        :rules="[rules.required]"
                                    />
                                    <v-text-field
                                        v-model="editedDocument.path"
                                        label="Link URL"
                                        hint="E.g.: http://www.google.com"
                                        persistent-hint
                                        required
                                        :rules="[rules.required]"
                                    />
                                </div>
                            </v-card-text>
                            <v-card-actions>
                                <v-spacer></v-spacer>
                                <v-btn :disabled="isLoading" color="blue darken-1" flat @click="close">Cancel</v-btn>
                                <v-btn :loading="isLoading" color="blue darken-1" flat @click="save">Save</v-btn>
                            </v-card-actions>
                        </v-form>
                    </v-card>
                </v-dialog>
                <v-data-table
                    :headers="headers"
                    :items="documents"
                    hide-actions
                    class="elevation-1"
                >
                    <template slot="items" slot-scope="data" @click="">
                        <td>
                            <v-tooltip right>
                                <v-icon slot="activator" :color="getType(data.item.path).color">{{getType(data.item.path).icon}}</v-icon>
                                <span>{{getType(data.item.path).type}}</span>
                            </v-tooltip>

                        </td>
                        <td><a href="#" @click="openDocument(data.item.path)">{{ data.item.name }}</a><v-icon class="ml-1" color="indigo" v-if="isNew(data.item.date_modified)">mdi-new-box</v-icon></td>
                        <!-- <td>{{ data.item.size }}</td> -->
                        <td>{{ formatDate(data.item.date_modified) }}</td>
                        <td class="justify-center layout px-0">
                            <v-btn icon class="mx-0" @click="editItem(data.item)">
                                <v-icon color="teal">edit</v-icon>
                            </v-btn>
                            <v-btn icon class="mx-0" @click="deleteItem(data.item)">
                                <v-icon color="pink">delete</v-icon>
                            </v-btn>
                        </td>
                    </template>
                  </v-data-table>
            </v-flex xs12>
        </v-layout row>
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
    data() {
        return {
            dialog: false,
            isLoading: false,
            docType: 'doc', // doc or link
            editedIndex: -1,
            formIsValid: false,
            editedDocument: {
                name: '',
                linkAddress: ''
            },
            defaultDocument: {
                name: '',
                linkAddress: ''
            },
            rules: {
                required(value) {
                    return !!value || 'Required.'
                }
            },
            documents: [],
            headers: [
                { text: 'Type', value: 'type', sortable: false },
                { text: 'Name', value: 'names' },
                // { text: 'Type', value: 'type' },
                // { text: 'Size', value: 'size' },
                { text: 'Last Updated', value: 'modifiedAt' },
                { text: 'Actions', value: 'name', sortable: false }
            ]
        }
    },
    props: ['collectionSlug'],
    computed: {
        formTitle () {
            return this.editedIndex === -1 ? 'New Document' : 'Edit Document'
        }
    },
    watch: {
        dialog (val) {
            val || this.close()
        }
    },
    created() {
        this.fetchDocumentsData()
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

        editItem (item) {
            this.editedIndex = this.documents.indexOf(item)
            this.editedItem = Object.assign({}, item)
            this.dialog = true
        },

        deleteItem (item) {
            // const index = this.documents.indexOf(item)
            // confirm('Are you sure you want to delete this document ?') && this.desserts.splice(index, 1)
        },

        close () {
            this.dialog = false
            setTimeout(() => {
                this.editedItem = Object.assign({}, this.defaultItem)
                this.editedIndex = -1
            }, 300)
        },

        save () {
            var self = this

            if (this.$refs.form.validate()) {
                // console.log('this.$refs.fileDrop.file', this.$refs.fileDrop.file)

                var payload = new FormData();
                payload.append('name', this.editedDocument.name);

                // TODO doc error red field if empty
                console.log('this.$refs.fileDrop.file', this.$refs.fileDrop.file)
                if(this.docType === 'doc' && this.$refs.fileDrop.file) {
                    // payload.file = this.$refs.fileDrop.file
                    payload.append('file', this.$refs.fileDrop.file)
                }
                else {
                    return
                }

                if(this.docType === 'link') {
                    payload.append('link', this.editedDocument.linkAddress)
                }

                this.isLoading = true

                Axios.post(Config.endpoint + 'media/documents/'+this.collectionSlug, payload)
                    .then(function (response) {
                        self.dialog = false
                        self.isLoading = false
                        self.fetchDocumentsData()
                    })
                    .catch(function (error) {
                        // TODO error management
                        self.isLoading = false
                    })
            }
        },

        openDocument(path) {
            window.open('http://api.turbojet.local/documents/'+path)
        },

        getType(path) {
            var arr = path.toLowerCase().split('.')

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
        },

        isNew(date) {
            return moment().diff(moment(date), 'days') <= 7
        }
    },
    components: {'file-drop': FileDrop}
}
</script>

<style scoped lang="scss">
    .page-list-documents {

    }
</style>
