
app.component('evaluation-method-technical--apply', {
    template: $TEMPLATES['evaluation-method-technical--apply'],

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
        const max = parseFloat($MAPAS.config.evaluationMethodTechnicalApply.max_result || "0.00");
        return {
            processing: false,
            vacancies: this.entity.parent?.vacancies || this.entity.vacancies,
            applyData: {
                from: [0, max],
                setStatusTo: '',
                cutoffScore: 0,
                earlyRegistrations: false,
                waitList: false,
                invalidateRegistrations: false,
                considerQuotas: true,
                quantityVacancies: 0
            },
            considerQuotas: true,
            enableConsiderQuotas: $MAPAS.config.evaluationMethodTechnicalApply.isAffirmativePoliciesActive,
            selectionType: [],
            tabSelected: 'score',
            cutoffScore: this.entity.evaluationMethodConfiguration?.cutoffScore ?? 0,
            api: new API()
        };
    },

    computed: {
        statusList() {
            return $MAPAS.config.evaluationMethodTechnicalApply.registrationStatusDict;
        },
        maxResult() {
            return parseFloat($MAPAS.config.evaluationMethodTechnicalApply.max_result || "0.00");
        },
    },

    methods: {
        validateValues() {
            const min = 0;
            const max = this.maxResult;

            if (this.applyData.from[0] < min) {
                this.applyData.from[0] = min;
            }
            if (this.applyData.from[1] > max) {
                this.applyData.from[1] = max;
            }
        },

        modalClose() {
            this.resetApplyData();
        },

        apply(modal, entity) {
            const messages = useMessages();
            
            this.infosApplyData();
            this.entity.disableMessages();
            this.processing = true;

            this.entity.POST('applyTechnicalEvaluation', {
                data: this.applyData, callback: data => {
                    this.processing = false;
                    messages.success(data);
                    modal.close();
                    this.reloadPage();
                    this.entity.enableMessages();
                }
            }).catch((data) => {
                this.processing = false;
                messages.error(data.data)
            });
        },

        reloadPage(timeout = 1500) {
            this.entities.refresh();
        },

        changed(event) {
            this.tabSelected = event.tab.slug;
        },

        setTabClassification() {
            this.applyData.cutoffScore = this.cutoffScore;
            this.applyData.earlyRegistrations = this.selectionType.includes('earlyRegistrations') ? true : false;
            this.applyData.waitList = this.selectionType.includes('waitList') ? true : false;
            this.applyData.invalidateRegistrations = this.selectionType.includes('invalidateRegistrations') ? true : false;
            this.applyData.considerQuotas = this.considerQuotas;
            this.applyData.quantityVacancies = this.vacancies;
            delete this.applyData.from;
            delete this.applyData.setStatusTo;
        },

        setTabScore() {
            const propertiesToRemove = ['cutoffScore', 'earlyRegistrations', 'waitList', 'invalidateRegistrations', 'considerQuotas'];

            for (const prop of propertiesToRemove) {
                if (this.applyData.hasOwnProperty(prop)) {
                    delete this.applyData[prop];
                }
            }
        },

        infosApplyData() {
            if (this.tabSelected === 'classification') {
                this.setTabClassification();
            } 
            
            if (this.tabSelected === 'score') {
                this.setTabScore();
            }

            this.applyData.tabSelected = this.tabSelected;
        },

        resetApplyData() {
            const max = this.maxResult;
            this.applyData = {
                from: [0, max],
                setStatusTo: '',
                cutoffScore: 0,
                earlyRegistrations: false,
                waitList: false,
                invalidateRegistrations: false,
                considerQuotas: true,
                quantityVacancies: this.vacancies
            };
            this.selectionType = [];
            this.tabSelected = 'score';
            this.cutoffScore = this.entity.evaluationMethodConfiguration?.cutoffScore ?? 0;
        }
    },
});
