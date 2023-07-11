app.component('event-importer-upload', {
    template: $TEMPLATES['event-importer-upload'],

    props: { },

    setup() {
        const text = Utils.getTexts('event-importer-upload')
        return { text }
    },
    data() {
        return {
            newFile: {},
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
        }
    },
    methods: {
        setFile() {
            this.newFile = this.$refs.file.files[0];
        },
        upload(modal){
            const _global = useGlobalState();
            const agent = _global.auth.user.profile;
            
            let data = {
                group: 'event-import-file',
                description: this.newFile.description
            };

            agent.upload(this.newFile, data).then((response) => {
                modal.close();
            });

            return true;
        },
    },
});
