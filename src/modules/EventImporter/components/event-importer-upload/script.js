app.component('event-importer-upload', {
    template: $TEMPLATES['event-importer-upload'],

    props: { 
        classes: {
            type: [String, Array, Object],
            required: false
        },
    },

    setup() {
        const text = Utils.getTexts('event-importer-upload')
        return { text }
    },
    data() {
        return {
            newFile: {},
            loading: false,
            maxFileSize: $MAPAS.maxUploadSizeFormatted,
        }
    },
    computed: {
        modalTitle() {
           return this.text('Importar arquivos de eventos')
        },
        csvUrl(){
            return Utils.createUrl('eventimporter', 'downloadExample', {type:"csv"});
        },
        xlsUrl(){
            return Utils.createUrl('eventimporter', 'downloadExample', {type:"xls"});
        },
        fileName(){
            return this.newFile.name ?? 'Selecione um arquivo';
        }
    },
    methods: {
        setFile() {
            this.newFile = this.$refs.file.files[0];
        },
        upload(modal){
            this.loading = true;
            const _global = useGlobalState();
            const agent = _global.auth.user.profile;
            
            let data = {
                group: 'event-import-file',
                description: this.newFile.description
            };

            agent.upload(this.newFile, data).then((response) => {
                this.newFile = {};
                this.loading = false;
                modal.close();
            });

            return true;
        },
        cancel(modal){
            this.newFile = {};
            modal.close();
        },
    },
});
