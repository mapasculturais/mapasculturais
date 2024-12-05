app.component('entity-file', {
    template: $TEMPLATES['entity-file'],
    emits: ['uploaded', 'setFile'],

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
        }
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

    methods: {
        setFile(event) {
            this.newFile = event.target.files[0];
            this.$emit('setFile', this.newFile);
        },

        async upload(modal) {
            this.loading = true;

            let data = {
                description: this.formData.description,
                group: this.groupName,
            };

            this.entity.disableMessages();
            try{

                const response = await this.entity.upload(this.newFile, data);
                this.$emit('uploaded', this);
                this.file = response;
                this.loading = false;
                this.entity.enableMessages();
                modal.close()

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

        deleteFile(file) {
            file.delete().then(() => {
                this.file = null;
            });
        }
    },
});
