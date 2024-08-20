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

    data() {
        return {
            processing: false,
            lastExported: $MAPAS.config.mcExportSpreadsheet.files ? $MAPAS.config.mcExportSpreadsheet.files[this.group] : [],
            interval: null
        }
    },
    
    methods: {
        exportSpreadsheet(type) {
            this.processing = 'exporting';
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
                this.processing = false;
            }).catch((data) => {
                this.messages.error(data.data);
            });
        },

        updateExportedData() {
            const api = new API('spreadsheets');
            let props = {
                entityType: this.owner.__objectType,
                id: this.owner.id,
                group: this.group,
            }
            let url = api.createUrl('filesByGroup', props);


            api.GET(url, props).then(res => res.json()).then(data => {
                this.lastExported = data;
            });
        },

        openModal() {
            this.interval = setInterval(() => {
                this.updateExportedData();
            }, 
            30 * 1000);
        },

        closeModal() {
            if(this.interval) {
                clearInterval(this.interval);
                this.interval = null;
            }
        }
    },
});
