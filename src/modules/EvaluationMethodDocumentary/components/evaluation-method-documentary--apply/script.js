app.component('evaluation-method-documentary--apply', {
    template: $TEMPLATES['evaluation-method-documentary--apply'],

    setup() {
        // os textos estão localizados no arquivo texts.php deste componente
        const text = Utils.getTexts('evaluation-method-documentary--apply')
        return { text }
    },

    props: {
        entity: {
            type: Entity,
            required: true
        }
    },

    data() {
        applyAll = false;
        applyData = {};
        return {
            applyData,
            applyAll
        }
    },

    computed: {
        modalTitle() {
            return this.text('modalTitle');
        },
        statusList() {
            return $MAPAS.config['evaluation-method-documentary--apply'].statusList;
        },
        consolidatedResults() {
            return $MAPAS.config['evaluation-method-documentary--apply'].consolidated_results;
        }
    },

    methods: {
        apply(modal) {
            this.applyData.status = this.applyAll ? 'all' : 'pending';
            this.entity.disableMessages();
            this.entity.POST('applyEvaluationsDocumentary', {
                data: this.applyData, callback: data => {
                    const messages = useMessages();
                    if (data.error) {
                        messages.error(data.data)
                    } else {
                        messages.success(data);
                        modal.close();
                        this.reloadPage();
                    }
                    this.entity.enableMessages();
                }
            })
        },
        valueToString(value) {
            switch (value) {
                case '0':
                    return this.text('Rascunho em análise');
                    break;
                case '1':
                    return this.text('Válida');
                    break;
                case '-1':
                    return this.text('Inválida');
                    break;
                default:
                    return value || '';
            }
        },
        reloadPage(timeout = 1500) {
            setTimeout(() => {
                document.location.reload(true)
            }, timeout);
        }
    },
});
