app.component('opportunity-form-import', {
    template: $TEMPLATES['opportunity-form-import'],


    emits: [],

    setup() {
        // os textos estão localizados no arquivo texts.php deste componente 
        const text = Utils.getTexts('opportunity-form-import');
        return { text }
    },

    created() {

    },

    computed: {
        files() {
            return this.entity.files?.[this.group] || []
        }
    },

    props: {
        entity: {
            type: Entity,
            required: true
        },
        classes: {
            type: [String, Array, Object],
            required: false
        },

    },

    data() {
        return {
            newFile: null,
            maxFileSize: $MAPAS.maxUploadSizeFormatted,
        }
    },

    methods: {
        setFile() {
            this.newFile = this.$refs.file.files[0];
        },

        async importFields(popover, close) {
            if (!this.newFile) {
                return;
            }

            const formData = new FormData();
            formData.append('fieldsFile', this.newFile);

            const api = new API('opportunity');
            const url = api.createUrl('importFields', { id: this.entity.id });

            try {
                const response = await api.POST(url, formData);
                const result = await response.json();

                if (result) {
                    const messages = useMessages();
                    messages.success(this.text('Importado com sucesso, você deve receber um e-mail ao terminar o processo de importação.'));
                }
            } catch (e) {
                console.error(e);
                const messages = useMessages();
                messages.error(this.text('Ocorreu um erro ao importar o formulário.'));
            } finally {
                close();
            }
        },
    },
});
