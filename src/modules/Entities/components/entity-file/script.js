app.component('entity-file', {
    template: $TEMPLATES['entity-file'],
    emits: ['uploaded'],

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
    },

    data() {
        return {
            formData: {},
            newFile: {},
            file: this.entity.files?.[this.groupName] || null,
            maxFileSize: $MAPAS.maxUploadSizeFormatted,
        }
    },

    methods: {
        setFile(event) {
            this.newFile = event.target.files[0];
        },

        upload(modal) {
            let data = {
                description: this.formData.description,
                group: this.groupName,
            };

            this.entity.upload(this.newFile, data).then((response) => {
                this.$emit('uploaded', this);
                this.file = response;
                modal.close()
            });

            return true;
        },

        deleteFile(file) {
            file.delete().then(() => {
                this.file = null;
            });
        }
    },
});
