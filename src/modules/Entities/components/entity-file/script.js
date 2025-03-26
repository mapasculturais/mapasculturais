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
