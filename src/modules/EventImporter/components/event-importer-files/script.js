app.component('event-importer-files', {
    template: $TEMPLATES['event-importer-files'],

    props: {},

    setup() {
        const text = Utils.getTexts('event-importer-files')
        return { text }
    },

    data() {
        return {
            newFile: {}
        }
    },

    computed: { 
        
    },

    methods: {
        getFiles(entity) {
            const files = entity.files['event-import-file'];
            return files;
        },
        processFile(file, entity) {
            const messages = useMessages();
            const url = Utils.createUrl('eventimporter', 'processFile');
            fetch(`${url.href}?file=${file.id}`)
                .then(async (res) => {
                    if (!res.ok) {
                        throw new Error(`HTTP ${res.status}`);
                    }
                    return res.json();
                })
                .then((response) => {
                    if (response.errors) {
                        file.errors = response.errors;
                        messages.error(this.text('processValidationError'));
                    } else {
                        file.errors = null;
                        entity.event_importer_processed_file = response;
                        messages.success(this.text('processSuccess'));
                    }
                })
                .catch((err) => {
                    console.error(err);
                    messages.error(this.text('processRequestError'));
                });
        },
        isProcessed(entity, file){
            return entity.event_importer_processed_file && entity.event_importer_processed_file[file.name] ? true : false;
        },
        processedDate(entity, file){
           return entity.event_importer_processed_file[file.name].date;
        }
    }
});
