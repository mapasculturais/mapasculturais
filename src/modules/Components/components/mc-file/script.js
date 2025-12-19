app.component('mc-file', {
    template: $TEMPLATES['mc-file'],
    emits: ['fileSelected'],

    props: {
        accept: {
            type: String,
            default: null,
        },
    },
    
    mounted() {
        window.addEventListener('mcFileClear', this.mcFileClear);
    },
    
    setup(props, { slots }) {
        const hasSlot = name => !!slots[name];
        const propId = Vue.useId()
        const text = Utils.getTexts('mc-file')
        return { propId, text, hasSlot }
    },

    data() {
        return {
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
        mcFileClear() {
            this.newFile = null
        },
        setFile(event) {
            this.newFile = event.target.files[0];
            this.$emit('fileSelected', this.newFile);
        },
    },
});
