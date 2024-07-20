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
        }
    },

    computed: {
        headers () {
            let itens = [
                { text: __('inscrição', 'opportunity-evaluations-table'), value: "registration.number", slug: "number", sticky: true, width: '160px' },
                { text: __('agente', 'opportunity-evaluations-table'), value: "registration.owner.name", slug: "agent"},
                { text: __('resultado final', 'opportunity-evaluations-table'), value: "evaluation.resultString", slug: "result"},
                { text: __('estado', 'opportunity-evaluations-table'), value: "evaluation.status", slug: "status"},
            ];

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
        createUrl(entity) {
            let user = this.user;
            if (user === 'all' && entity.evaluation?.status == null) {
                return 'javascript:void(0)';
            } else if (user === 'all' && entity.evaluation) {
                user = entity.evaluation?.user;
            }
            
            return Utils.createUrl('registration', 'evaluation', { id: entity.registration.id, user });
        },
        canSee(action) {
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
            registration.evaluation = rawData.evaluation;

            return registration;
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