app.component('mc-export-spreadsheet', {
    template: $TEMPLATES['mc-export-spreadsheet'],

    props: {
        owner: {
            type: Entity,
            required: true,
        },

        endpoint: {
            type: String,
            required: true,
        },

        params: {
            type: Object,
            required: true,
        },

        group: {
            type: String,
            required: true,
        },

        showExportedFiles: {
            type: Boolean,
            default: true, // remover
        },
    },
    
    setup() {
        const messages = useMessages();
        return { messages }
    },

    computed: {
        lastExported() {
            return this.owner.files[this.group] ?? [];
        }
    },
    
    methods: {
        exportSpreadsheet(type) {
            const api = new API();
            let url = Utils.createUrl('spreadsheets', this.endpoint);

            let props = {
                ...this.params,
                ownerType: this.owner.__objectType,
                ownerId: this.owner.id,
                extension: type,
            }

            api.POST(url, props).then((data) => {
                this.messages.success(__('sucesso', 'mc-export-spreadsheet'))
            }).catch((data) => {
                this.messages.error(data.data);
            });
        }
    },
});
