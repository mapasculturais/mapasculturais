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
        resultLabels() {
            return $MAPAS.config['evaluation-method-continuous--apply'].resultLabels;
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
            return this.resultLabels[value] ?? (value || '');
        },
        reloadPage(timeout = 1500) {
            this.entities.refresh();
        }
    },
});
