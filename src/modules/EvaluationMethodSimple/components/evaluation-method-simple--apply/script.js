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
        return {
            applyData: {},
            applyAll: false,
            registrationListText: '',
            tabSelected: 'result'
        };
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

            this.prepareApplyData();
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
        changed(event) {
            this.tabSelected = event.tab.slug;
        },
        modalClose() {
            this.resetApplyData();
        },
        parseRegistrationList(text) {
            if (!text || !text.trim()) {
                return [];
            }

            return text
                .split(/[,;\s\n\r]+/)
                .map(number => number.trim())
                .filter(number => number.length > 0);
        },
        prepareApplyData() {
            this.applyData.tabSelected = this.tabSelected;

            if (this.tabSelected === 'registration') {
                this.applyData.registrationNumbers = this.parseRegistrationList(this.registrationListText);
                delete this.applyData.from;
                delete this.applyData.status;
                return;
            }

            this.applyData.status = this.applyAll ? 'all' : 'pending';
            delete this.applyData.registrationNumbers;
        },
        resetApplyData() {
            this.applyData = {};
            this.applyAll = false;
            this.registrationListText = '';
            this.tabSelected = 'result';
        },
        valueToString(value) {
            return this.resultLabels[value] ?? (value || '');
        },
        reloadPage(timeout = 1500) {
            this.entities.refresh();
        }
    },
});
