app.component('opportunity-rules', {
    template: $TEMPLATES['opportunity-rules'],

    setup() { 
        // os textos estão localizados no arquivo texts.php deste componente 
        const text = Utils.getTexts('opportunity-rules')
        return { text }
    },

    computed: {
        file() {
            return this.entity.files?.['rules'] || null
        }
    },

    props: {
        entity: {
            type: Entity,
            required: true
        },
        title: {
            type: String,
            required: true
        },
        editable: {
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
                group: 'rules'
            };

            this.entity.upload(this.newFile, data).then((response) => {
                this.$emit('uploaded', this);
                popover.close()
            });

            return true;
        },
    },
});
