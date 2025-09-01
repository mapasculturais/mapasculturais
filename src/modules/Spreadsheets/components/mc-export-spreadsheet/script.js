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
        let group = this.group;
        if(this.group == "evaluations-spreadsheets" && $MAPAS.config.mcExportSpreadsheet.evaluation_type) { 
            group = $MAPAS.config.mcExportSpreadsheet.evaluation_type
        }

        return {
            isOpenModal: false,
            processing: false,
            lastExported: $MAPAS.config.mcExportSpreadsheet.files ? $MAPAS.config.mcExportSpreadsheet.files[group] : [],
            interval: null,
            exportReturn: null,
        }
    },
    
    methods: {
        exportSpreadsheet(type, modal) {
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
                this.messages.success(__('sucesso', 'mc-export-spreadsheet'), 5000)
                this.processing = false;

                this.$nextTick(() => {
                    this.exportReturn = __('sucesso', 'mc-export-spreadsheet');
                    setTimeout(() => {
                        this.exportReturn = null;
                        modal.close();
                    }, 7000);
                });

            }).catch((data) => {
                this.messages.error(data.data);
            });
        },

        updateExportedDataTimeout() {
            this.interval = setInterval(() => {
               this.getExportData();
            }, 
            30 * 1000);
        },

        getExportData() {
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
            if (this.isOpenModal) { return };
            this.isOpenModal = true;
            this.getExportData();
            this.updateExportedDataTimeout();
        },

        closeModal() {
            if(this.interval) {
                clearInterval(this.interval);
                this.interval = null;
            }
        }
    },
});
