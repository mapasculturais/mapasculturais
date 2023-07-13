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
            const url = Utils.createUrl('eventimporter', 'processFile');
            fetch(`${url.href}?file=${file.id}`).then(res => res.json()).then((response) => {
                console.log(response)
                if(response.errors){
                    file.errors = response.errors;
                }else{
                    entity.event_importer_processed_file = response;
                }
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