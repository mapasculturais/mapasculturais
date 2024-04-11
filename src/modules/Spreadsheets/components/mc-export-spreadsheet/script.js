app.component('mc-export-spreadsheet', {
    template: $TEMPLATES['mc-export-spreadsheet'],

    props: {
        entity: {
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
        },
    },
    
    setup() {
        const messages = useMessages();
        return { messages }
    },

    computed: {
        lastExported() {
            return this.entity.files[this.group] ?? [];
        }
    },
    
    methods: {
        exportSpreadsheet(type) {
            const api = new API();
            let url = Utils.createUrl('spreadsheets', this.endpoint);

            let props = {
                ...this.params,
                extension: type,
            }

            api.POST(url, props).catch((data) => {
                messages.error(data.data);
            });

        }
    },
});
