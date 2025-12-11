app.component('entity-file', {
    template: $TEMPLATES['entity-file'],
    emits: ['delete', 'setFile', 'uploaded'],

    setup(props, {slots}) {
        // os textos estão localizados no arquivo texts.php deste componente 
        const text = Utils.getTexts('entity-file');
        const hasSlot = name => !!slots[name];
        return { text, hasSlot}
    },

    props: {
        entity: {
            type: Entity,
            required: true
        },
        groupName: {
            type: String,
            required: true
        },
        titleModal: {
            type: String,
            default: ""
        },
        title: {
            type: String,
            default: ""
        },
        description: {
            type: String
        },
        uploadFormTitle: {
            type: String,
            required: false
        },
        required: {
            type: Boolean,
            require: false
        },
        editable: {
            type: Boolean,
            require: false
        },
        disableName: {
            type: Boolean,
            default: false
        },
        enableDescription: {
            type: Boolean,
            default: false
        },
        classes: {
            type: [String, Array, Object],
            required: false
        },
        downloadOnly: {
            type: Boolean,
            default: false,
        },
        disabled: {
            type: Boolean,
            default: false,
        },
        defaultFile: {
            type: Object,
            required: false
        },
        beforeUpload: {
            type: Function,
            required: false
        },
        uploadOnSubmit: {
            type: Boolean,
            default: true,
        },
        buttonTextValue: {
            type: String,
            required: false,
            default: 'Enviar'
        },
        allowedFileTypes: {
            type: Array,
            required: false,
            default: () => []
        },
    },

    data() {
        return {
            formData: {},
            newFile: {},
            file: this.entity.files?.[this.groupName] || null,
            maxFileSize: $MAPAS.maxUploadSizeFormatted,
            loading: false
        }
    },

    computed: {
        acceptAttribute() {
            if (this.allowedFileTypes && this.allowedFileTypes.length > 0) {
                return this.allowedFileTypes.join(',');
            }
            return null;
        },
        allowedFileTypesLabel() {
            if (!this.allowedFileTypes || this.allowedFileTypes.length === 0) {
                return '';
            }
            
            const mimeToExtension = {
                'application/pdf': 'PDF',
                'image/jpeg': 'JPEG',
                'image/png': 'PNG',
                'image/gif': 'GIF',
                'application/msword': 'DOC',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document': 'DOCX',
                'application/vnd.oasis.opendocument.text': 'ODT',
                'application/vnd.ms-excel': 'XLS',
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet': 'XLSX',
                'application/vnd.oasis.opendocument.spreadsheet': 'ODS',
                'text/csv': 'CSV',
                'application/vnd.ms-powerpoint': 'PPT',
                'application/vnd.openxmlformats-officedocument.presentationml.presentation': 'PPTX',
                'application/vnd.oasis.opendocument.presentation': 'ODP',
                'application/zip': 'ZIP',
                'application/x-rar-compressed': 'RAR',
                'video/mp4': 'MP4',
                'video/x-msvideo': 'AVI',
                'video/quicktime': 'MOV',
                'audio/mpeg': 'MP3',
                'audio/wav': 'WAV',
                'text/plain': 'TXT'
            };
            
            const extensions = this.allowedFileTypes.map(mime => 
                mimeToExtension[mime] || mime
            );
            
            return extensions.join(', ');
        }
    },

    updated() {
        if (this.uploadOnSubmit) {
            this.file = this.entity.files?.[this.groupName] || null;
        }
    },

    methods: {
        setFile(event) {
            this.newFile = event.target.files[0];

            if (!this.uploadOnSubmit && this.newFile) {
                this.file = this.newFile;
            }

            this.$emit('setFile', this.newFile);
        },

        async upload(modal) {
            this.loading = true;

            let data = {
                description: this.formData.description,
                group: this.groupName,
            };

            if (this.beforeUpload) {
                await this.beforeUpload({
                    data,
                    file: this.newFile
                });
            }

            this.entity.disableMessages();
            try{
                const response = await this.entity.upload(this.newFile, data);
                this.file = response;
                this.$emit('uploaded', this);
                this.loading = false;
                this.entity.enableMessages();

                this.file = null;
                this.newFile = {};

                if (modal) {
                    modal.close();
                }

            } catch(e) {
                this.loading = false;
                if(e.error) {
                    const messages = useMessages();
                    messages.error(e.data[this.groupName]);
                } else {
                    console.error(e);
                }
            }

            return true;
        },

        async submit(modal) {
            if (this.uploadOnSubmit) {
                await this.upload(modal);
            } else {
                modal.close();
            }
        },

        async deleteFile(file) {
            await file.delete();
            this.file = null;
            this.newFile = {};

            this.$emit('delete', file);
        }
    },
});
