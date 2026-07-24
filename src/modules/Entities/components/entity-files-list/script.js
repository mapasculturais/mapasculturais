app.component('entity-files-list', {
    template: $TEMPLATES['entity-files-list'],
    emits: ['uploaded'],

    setup() { 
        // os textos estão localizados no arquivo texts.php deste componente 
        const text = Utils.getTexts('entity-files-list');
        const messages = useMessages();
        return { text, messages }
    },

    created() {

    },

    mounted() {
    },

    computed: {
        isDocsAnexoList() {
            const classes = this.classes;

            if (typeof classes === 'string') {
                return classes.includes('docs-anexo-list');
            }

            if (Array.isArray(classes)) {
                return classes.includes('docs-anexo-list');
            }

            return classes?.['docs-anexo-list'] ?? false;
        },
    },

    props: {
        entity: {
            type: Entity,
            required: true
        },
        group: {
            type: String,
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
        hideTitle: {
            type: Boolean,
            default: false
        },

        /** Exibe ação de visualização (layout público). */
        viewAction: {
            type: Boolean,
            default: false
        },

        /** Texto da ação de visualização (ex.: "ver arquivo", "ver documento"). */
        viewActionLabel: {
            type: String,
            default: __('ver arquivo', 'entity-files-list')
        },

        /** Metadado do agente associado ao FileGroup (ex.: cpfAnexo para docs-cpf). */
        sealProp: {
            type: String,
            required: false,
        },

    },
    
    data() {
        return {
            newFile: {},
            maxFileSize: $MAPAS.maxUploadSizeFormatted,
        }
    },

    methods: {
        getFiles(){
            if(!this.entity.files?.[this.group]){
                return null;
            }
            
            if(this.entity.files?.[this.group] instanceof Array){
                return this.entity.files?.[this.group];
            } else {
                return [this.entity.files?.[this.group]]
            }
        },

        setFile() {
            let description = this.newFile.description;
            this.newFile = this.$refs.file.files[0];
            this.newFile.description = description;
        },

        upload(popover) {
            let data = {
                group: this.group, 
                description: this.newFile.description
            };

            this.entity.upload(this.newFile, data).then((response) => {
                this.$emit('uploaded', this);
                popover.close();
            })
            .catch((error) => {
                const groupMessages = error.data?.[this.group];
                if (Array.isArray(groupMessages)) {
                    for (const message of groupMessages) {
                        this.messages.error(message);
                    }
                } else if (groupMessages) {
                    this.messages.error(groupMessages);
                }
            });

            return true;
        },

        rename(file, popopver) {
            file.description = file.newDescription;
            file.save().then(() => popopver.close());
        },
    },
});
