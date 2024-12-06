app.component('opportunity-evaluations-table', {
    template: $TEMPLATES['opportunity-evaluations-table'],

    props: {
        phase: {
            type: Entity,
            required: true
        },
        classes: {
            type: [String, Array, Object],
            required: false
        },
        user: {
            type: [String, Number],
            required: true
        },
        identifier: {
            type: String,
            required: true,
        },
    },

    setup() {
        // os textos estão localizados no arquivo texts.php deste componente
        const text = Utils.getTexts('opportunity-evaluations-table');
        return { text }
    },

    data() {
        return {
            query: {
                '@opportunity': this.phase.opportunity.id,
            },
            locale: $MAPAS.config.locale,
            firstDate: null,
            lastDate: null,
            selectedStatus: null,
            evaluatiorFilter: null,
        }
    },

    computed: {
        hasControl() {
            return this.phase.opportunity.currentUserPermissions['@control'];
        },
        evaluationsFiltersOptions() {
            return $MAPAS.config.opportunityEvaluationsTable.committee
        },
        filtersDictComplement() {
            let committee = $MAPAS.config.opportunityEvaluationsTable.committee;
            let result = {};
            for(const item of committee) {
                result[item.value] = item.label 
                
            }

            return result;
        },
        headers () {
            let itens = [
                { text: __('inscrição', 'opportunity-evaluations-table'), value: "number", slug: "number", sticky: true, width: '160px' },
                { text: __('avaliador', 'opportunity-evaluations-table'), value: "valuer?.name", slug: "evaluator", visible: true},
                { text: __('resultado final', 'opportunity-evaluations-table'), value: "evaluation?.resultString", slug: "result"},
                { text: __('estado', 'opportunity-evaluations-table'), value: "evaluation?.status", slug: "status"},
            ];

            if(this.avaliableEvaluationFields('agentsSummary')) {
                itens.splice(2, 0, { text: __('agente', 'opportunity-evaluations-table'), value: "agentsData.owner?.name", slug: "agent"});
                itens.splice(2, 0, { text: __('coletivo', 'opportunity-evaluations-table'), value: "agentsData.coletivo?.name", slug: "coletivo"});
            }

            return itens;
        },

        status() {
            return [
                {
                    value: 'all',
                    label: __('Todas', 'opportunity-evaluations-table'),
                },
                {
                    value: 'pending',
                    label: __('Avaliações pendentes', 'opportunity-evaluations-table'),
                },
                {
                    value: '0',
                    label: __('Avaliações iniciadas', 'opportunity-evaluations-table'),
                },
                {
                    value: 1,
                    label: __('Avaliações concluídas', 'opportunity-evaluations-table'),
                },
                {
                    value: 2,
                    label: __('Avaliações enviadas', 'opportunity-evaluations-table'),
                },
            ]
        },
    },
    
    methods: {
        valuersMetadata() {
            if(this.user != "all") {
                return $MAPAS.config.opportunityEvaluationsTable.valuersMetadata[this.user]
            }

            return null;
        },
        avaliableEvaluationFields(field) {
            if(this.phase.opportunity.currentUserPermissions['@control']) {
                return true;
            }
            
            return this.phase.opportunity.avaliableEvaluationFields[field]
        },
        createUrl(entity) {
            let user = this.user;
            if (user === 'all' && entity.evaluation?.status == null) {
                return 'javascript:void(0)';
            } else if (user === 'all' && entity.evaluation) {
                user = entity.evaluation?.user;
            }
            
            return Utils.createUrl('registration', 'evaluation', { id: entity._id, user });
        },
        canSee(action) {
            let metadata = this.valuersMetadata();
            if(metadata && metadata.summary.completed <= 0) {
                return false
            }

            if (this.phase.opportunity.currentUserPermissions[action]) {
                return true;
            }
            return false
        },
        
        isFuture() {
            return this.phase.evaluationFrom?.isFuture();
        },

        isHappening() {
            return this.phase.evaluationFrom?.isPast() && this.phase.evaluationTo?.isFuture();
        },

        isPast() {
            return this.phase.evaluationTo?.isPast();
        },

        rawProcessor(rawData) {
            const registrationApi = new API('registration');
            const registration = registrationApi.getEntityInstance(rawData.registration.id);
            registration.populate(rawData.registration, true);
            
            let reg = {};
            reg = {...registration};

            reg.evaluation = rawData.evaluation;
            reg.valuer = rawData.valuer;

            return reg;
        },

        getStatus(status) {
            switch(status) {
                case 0:
                    return  __('Avaliação iniciada', 'opportunity-evaluations-table');
                case 1:
                    return  __('Avaliação concluída', 'opportunity-evaluations-table');
                case 2:
                    return  __('Avaliação enviada', 'opportunity-evaluations-table');
                default:
                    return  __('Avaliação pendente', 'opportunity-evaluations-table');
            }
        },

        getResultString(result) {
            return result ?? __('não avaliado', 'opportunity-evaluations-table');
        },

        filterByStatus(option, entities) {
            this.selectedStatus = option.value;

            if (this.selectedStatus.length > 0) {
                this.query['@filterStatus'] = `${this.selectedStatus.toString()}`;
            } else {
                delete this.query['@filterStatus'];
            }

            entities.refresh();
        },

        filterByEvaluator(option, entities) {
            this.evaluatiorFilter = option.value;

            if (this.evaluatiorFilter != "all") {
                this.query['@evaluationId'] = `${this.evaluatiorFilter.toString()}`;
            } else {
                delete this.query['@evaluationId'];
            }

            entities.refresh();
        },

        dateFormat(date) {
            let mcdate = new McDate (date);
            return mcdate.date('2-digit year');
        },

        onChange(event, onInput, entities) {
            if(event instanceof InputEvent) {
                setTimeout(() => onInput(event), 50);
            }

            if (this.firstDate && this.lastDate) {
                this.query['@date'] = `BETWEEN '${this.dateFormat(this.firstDate)}' AND '${this.dateFormat(this.lastDate)}'`
            } else if (this.firstDate) {
                this.query['@date'] = `>= '${this.dateFormat(this.firstDate)}'`;
            } else if (this.lastDate) {
                this.query['@date'] = `<= '${this.dateFormat(this.lastDate)}'`;
            } else {
                delete this.query['@date'];
            }
            
            entities.refresh();
        },

        clearFilters(entities) {
            this.firstDate = null;
            this.lastDate = null;
            this.selectedStatus = null;
            delete this.query['status'];
            delete this.query['@date'];

            entities.refresh();
        },

        removeFilter(filter) {
            if (filter.prop == '@date') {
                if (filter.value.includes('>=')) {
                    this.firstDate = null;
                }

                if (filter.value.includes('<=')) {
                    this.lastDate = null;
                }

                if (filter.value.includes('BETWEEN')) {
                    this.firstDate = null;
                    this.lastDate = null;
                }
            
                delete this.query['@date'];
            }

            if (filter.prop == 'status' || filter.prop == '@pending') {
                this.selectedStatus = null;
                delete this.query['status'];
            }
        }
    }
});