app.component('evaluation-method-simple--apply', {
    template: $TEMPLATES['evaluation-method-simple--apply'],

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
            return $MAPAS.config['evaluation-method-simple--apply'].statusList;
        },
        resultLabels() {
            return $MAPAS.config['evaluation-method-simple--apply'].resultLabels;
        },
        consolidatedResults() {
            return $MAPAS.config['evaluation-method-simple--apply'].consolidated_results;
        }
    },

    methods: {
        apply(modal) {
            const messages = useMessages();

            this.applyData.status = this.applyAll ? 'all' : 'pending';
            this.entity.disableMessages();
            
            this.entity.POST('applyEvaluationsSimple', {
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
