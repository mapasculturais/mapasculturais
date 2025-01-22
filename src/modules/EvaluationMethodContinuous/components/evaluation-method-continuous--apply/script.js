app.component('evaluation-method-continuous--apply', {
    template: $TEMPLATES['evaluation-method-continuous--apply'],

    setup() {
        // os textos estão localizados no arquivo texts.php deste componente
        const text = Utils.getTexts('evaluation-method-continuous--apply')
        return { text }
    },

    props: {
        entity: {
            type: Entity,
            required: true
        },

        entities: {
            type: Array,
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
        statusList() {
            return $MAPAS.config['evaluation-method-continuous--apply'].statusList;
        },
        consolidatedResults() {
            return $MAPAS.config['evaluation-method-continuous--apply'].consolidated_results;
        }
    },

    methods: {
        apply(modal) {
            const messages = useMessages();

            this.applyData.status = this.applyAll ? 'all' : 'pending';
            this.entity.disableMessages();
            
            this.entity.POST('applyEvaluationsContinuous', {
                data: this.applyData, callback: data => {
                    messages.success(data);
                    modal.close();
                    this.reloadPage();
                    this.entity.enableMessages();
                }
            }).catch((data) => {
                messages.error(data.data)
            });
        },
        valueToString(value) {
            switch (value) {
                case '0':
                    return this.text('Rascunho em análise');
                    break;
                case '2':
                    return this.text('Inválida');
                    break;
                case '3':
                    return this.text('Não selecionada');
                    break;
                case '10':
                    return this.text('Selecionada');
                    break;
                default:
                    return value || '';
            }
        },
        reloadPage(timeout = 1500) {
            this.entities.refresh();
        }
    },
});
