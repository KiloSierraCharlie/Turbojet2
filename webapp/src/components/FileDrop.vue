<template>
    <!-- https://css-tricks.com/examples/DragAndDropFileUploading/ -->
    <div class="file-drop mt-3">
        <div class="label subheading">{{label}}</div>
        <form class="box" :class="{'is-dragover': isDragOver}" method="post" action="" enctype="multipart/form-data"
            v-on:dragover.stop.prevent="isDragOver=true"
            v-on:dragenter.stop.prevent="isDragOver=true"
            v-on:dragend.stop.prevent="isDragOver=false"
            v-on:dragleave.stop.prevent="isDragOver=false"
            v-on:drop.stop.prevent="onFilesDropped"
            v-on:drag.stop.prevent
            v-on:dragstart.stop.prevent
        >
            <div>
                <svg class="icon" xmlns="http://www.w3.org/2000/svg" width="50" height="43" viewBox="0 0 50 43"><path d="M48.4 26.5c-.9 0-1.7.7-1.7 1.7v11.6h-43.3v-11.6c0-.9-.7-1.7-1.7-1.7s-1.7.7-1.7 1.7v13.2c0 .9.7 1.7 1.7 1.7h46.7c.9 0 1.7-.7 1.7-1.7v-13.2c0-1-.7-1.7-1.7-1.7zm-24.5 6.1c.3.3.8.5 1.2.5.4 0 .9-.2 1.2-.5l10-11.6c.7-.7.7-1.7 0-2.4s-1.7-.7-2.4 0l-7.1 8.3v-25.3c0-.9-.7-1.7-1.7-1.7s-1.7.7-1.7 1.7v25.3l-7.1-8.3c-.7-.7-1.7-.7-2.4 0s-.7 1.7 0 2.4l10 11.6z"></path></svg>
                <input class="file" type="file" name="files[]" id="file" data-multiple-caption="{count} files selected" multiple />
                <label for="file">
                    <span v-if="!errorMessage && droppedFiles.length === 0">
                        <strong>Choose a file</strong><span class="dragndrop"> or drag it here</span><br>
                        <span class="caption">(XX Mb max. Authorized formats are JPEG, JPG, PNG, PDF, ZIP, WORD, XLS, PPT)</span>
                    </span>
                    <span v-if="errorMessage" v-html="errorMessage"/>
                    <span v-if="!errorMessage && droppedFiles.length === 1"><strong>{{this.file.name}}</strong></span>
                </label>
            </div>
        </form>
    </div>

</template>

<script>
import _ from 'lodash'

export default {
    props: ['label', 'allowed-types'],
    name: 'file-drop',
    data () {
        return {
            allowedTypesImages: [
                'image/jpeg',
                'image/png'
            ],
            allowedTypesOthers: [
                'application/pdf',
                'multipart/x-zip',
                'application/zip',
                'application/x-zip-compressed',
                'application/x-compressed',
                'application/msword',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'application/excel',
                'application/vnd.ms-excel',
                'application/x-excel',
                'application/x-msexcel',
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'application/vnd.ms-powerpoint',
                'application/vnd.openxmlformats-officedocument.presentationml.presentation',
                'application/vnd.openxmlformats-officedocument.presentationml.slideshowapplication/vnd.ms-powerpoint'
            ],
            processQueue: false,
            isDragOver: false,
            droppedFiles: [],
            errorMessage: ''
        }
    },
    methods: {
        onFilesDropped(e) {
            console.log('onFilesDropped', e)
            this.isDragOver = false
            this.errorMessage = ''

            if(e.dataTransfer.files.length > 1) {
                this.errorMessage = '<strong>Error:</strong>You can only upload one file at a time. <strong>Try again!</strong>'
                return
            }

            if(!this.isValidType(e.dataTransfer.files[0])) {
                this.errorMessage = '<strong>Error:</strong>This format is not allowed. Please use one of the following: JPEG, JPG, PNG, PDF, ZIP, WORD, XLS, PPT'
                return
            }
            else {
                this.droppedFiles = e.dataTransfer.files
                this.file = e.dataTransfer.files[0]
            }
        },

        isValid() {
            return this.droppedFiles.length === 1 && this.file && this.isValidType(this.file)
        },

        isValidType(file) {
            // Retrain types to imaes only or all depending on the allowed-type parameter
            var mergedAllowedTypes = this.allowedTypes === 'images' ? this.allowedTypesImages : _.concat(this.allowedTypesImages, this.allowedTypesOthers)
            console.log('mergedAllowedTypes', this.allowedTypes, mergedAllowedTypes)
            return _.indexOf(mergedAllowedTypes, file.type) !== -1
        }
    }
}
</script>

<style scoped lang="scss">
@import '~scss/variables';

.file-drop {
    .label {
        color: rgba(0,0,0,0.54);
    }
    form {
        position: relative;
        width: 100%;
        height: 250px;
        padding: 60px 20px;
        text-align: center;
        color: #546E7A;
        background-color: #c9dae0;
        outline: 2px dashed #92b0b3;
        outline-offset: -10px;

        &.is-dragover {
            outline-offset: -12px;
            outline: 2px dashed #78909C;
            color: #455A64;

            .icon {
                fill: #78909C;
            }
        }

        .dragndrop {
          display: inline;
        }

        .icon {
            width: 100%;
            height: 60px;
            fill: #92b0b3;
            display: block;
        }

        .file {
            width: 0.1px;
            height: 0.1px;
            opacity: 0;
            overflow: hidden;
            position: absolute;
            z-index: -1;
        }
        .file + label {
            margin-top: 30px;
            max-width: 80%;
            // text-overflow: ellipsis;
            // white-space: nowrap;
            cursor: pointer;
            display: inline-block;
            overflow: hidden;
        }

        .file + label:hover strong,
        .file:focus + label strong,
        .file.has-focus + label strong {
            color: #39bfd3;
        }
        .file:focus + label,
        .file.has-focus + label {
            outline: 1px dotted #000;
            outline: -webkit-focus-ring-color auto 5px;
        }
        .file + label * {
            /* pointer-events: none; */ /* in case of FastClick lib use */
        }

        -webkit-transition: outline-offset .15s ease-in-out, color .15s linear;
        transition: outline-offset .15s ease-in-out, color .15s linear;
    }
}
</style>
