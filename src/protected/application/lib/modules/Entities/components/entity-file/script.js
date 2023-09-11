app.component('entity-file', {
    template: $TEMPLATES['entity-file'],
    emits: ['uploaded'],

    setup() {
        // os textos estão localizados no arquivo texts.php deste componente 
        const text = Utils.getTexts('entity-file')
        return { text }
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
    },

    data() {
        return {
            formData: {},
            newFile: {},
            file: this.entity.files?.[this.groupName] || null,
        }
    },

    methods: {
        setFile(event) {
            this.newFile = event.target.files[0];
        },

        upload(popover) {
            let data = {
                description: this.formData.description,
                group: this.groupName,
            };

            this.entity.upload(this.newFile, data).then((response) => {
                this.$emit('uploaded', this);
                this.file = response;
                popover.close()
            });

            return true;
        },
    },
});
