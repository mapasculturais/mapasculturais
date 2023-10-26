app.component('opportunity-form-import', {
    template: $TEMPLATES['opportunity-form-import'],


    emits: [],

    setup() {
        // os textos estão localizados no arquivo texts.php deste componente 
        const text = Utils.getTexts('entity-files-list')
        return { text }
    },

    created() {

    },

    computed: {
        files() {
            return this.entity.files?.[this.group] || []
        }
    },

    props: {
        entity: {
            type: Entity,
            required: true
        },
        classes: {
            type: [String, Array, Object],
            required: false
        },

    },

    data() {
        return {
            newFile: {},
            maxFileSize: $MAPAS.maxUploadSizeFormatted,
        }
    },

    methods: {
        setFile() {
            this.newFile = this.$refs.file.files[0];
        },

        upload(popover) {
            let data = {
                group: this.group,
                description: this.newFile.description
            };

            this.entity.upload(this.newFile, data).then((response) => {
                this.$emit('uploaded', this);
                popover.close()
            });

            return true;
        },

        rename(file, popopver) {
            file.description = file.newDescription;
            file.save().then(() => popopver.close());
        }
    },
});
