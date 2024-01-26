app.component('mc-file', {
    template: $TEMPLATES['mc-file'],
    emits: ['fileSelected'],
    
    setup(props, { slots }) {
        const hasSlot = name => !!slots[name];
        const text = Utils.getTexts('mc-file')
        return { text, hasSlot }
    },

    data() {
        return {
            uniqueId: 'newFile' + (Math.floor(Math.random() * 9000) + 1000),
            newFile: null,
            maxFileSize: $MAPAS.maxUploadSizeFormatted,
        }
    },

    computed: {
        fileName() {
            return this.newFile ? this.newFile.name : __('sem arquivo', 'mc-file');
        }
    },
    
    methods: {
        setFile(event) {
            this.newFile = event.target.files[0];
            this.$emit('fileSelected', this.newFile);
        },
    },
});
