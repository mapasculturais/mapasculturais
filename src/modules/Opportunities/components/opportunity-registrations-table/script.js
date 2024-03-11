app.component('opportunity-registrations-table', {
    template: $TEMPLATES['opportunity-registrations-table'],
    props: {
        phase: {
            type: Entity,
            required: true
        }
    },
    setup() {
        // os textos estão localizados no arquivo texts.php deste componente
        const text = Utils.getTexts('opportunity-registrations-table');
        return { text }
    },
    data() {
        const $DESC = $DESCRIPTIONS.registration;
        const avaliableFields = [];
        
        if(this.phase.registrationCategories?.length > 0) {
            avaliableFields.push({
                title: $DESC.category.label,
                fieldName: 'category',
                fieldOptions: this.phase.registrationCategories,
            });
        }

        if(this.phase.registrationProponentTypes?.length > 0) {
            avaliableFields.push({
                title: $DESC.proponentType.label,
                fieldName: 'proponentType',
                fieldOptions: this.phase.registrationProponentTypes,
            });
        }

        if(this.phase.registrationRanges?.length > 0) {
            avaliableFields.push({
                title: $DESC.range.label,
                fieldName: 'range',
                fieldOptions: this.phase.registrationRanges.map((item) => item.label),
            });
        }

        const fieldTypes = ['select', 'boolean', 'checkbox', 'multiselect', 'checkboxes', 'agent-owner-field', 'agent-collective-field'];
        for(let key of Object.keys($DESC)) {
            const field = $DESC[key];

            if(key.startsWith('field_') && fieldTypes.indexOf(field.field_type) >= 0) {
                if(field.field_type == 'agent-owner-field' || field.field_type == 'agent-collective-field') {
                    const angentFieldDescription = $DESCRIPTIONS.agent[field.registrationFieldConfiguration?.config?.entityField];
                    if(angentFieldDescription) {
                        avaliableFields.push(field.registrationFieldConfiguration);
                    }
                } else {
                    avaliableFields.push(field.registrationFieldConfiguration);
                }
            }
        }

        const sortOptions = [
            { order: 'status DESC,consolidatedResult AS FLOAT DESC', label: 'por status descendente' },
            { order: 'status ASC,consolidatedResult AS FLOAT ASC', label: 'por status ascendente' },
            { order: 'consolidatedResult AS FLOAT DESC', label: 'resultado das avaliações' },
            { order: 'score DESC', label: 'pontuação final' },
            { order: '@quota', label: 'pontuação final CONSIDERANDO COTAS' },
            { order: 'createTimestamp ASC', label: 'mais antigas primeiro' },
            { order: 'createTimestamp DESC', label: 'mais recentes primeiro' },
            { order: 'sentTimestamp ASC', label: 'enviadas a mais tempo primeiro' },
            { order: 'sentTimestamp DESC', label: 'enviadas a menos tempo primeiro' },
        ];

        return {
            sortOptions,
            filters: {},
            resultStatus:[],
            query: {
                '@opportunity': this.phase.id,
            },
            selectedCategories: [],
            selectedProponentTypes: [],
            selectedRanges: [],
            selectedStatus: [],
            selectedAvaliation:null,
            order: 'consolidatedResult AS FLOAT DESC',
            avaliableFields
        }
    },

    computed: {
        statusDict() {
            return $MAPAS.config.opportunityRegistrationTable.registrationStatusDict;
        },
        statusEvaluation () {
            return $MAPAS.config.opportunityRegistrationTable.evaluationStatusDict;
        },
        statusEvaluationResult () {
            let evaluationType = this.phase.evaluationMethodConfiguration ? this.phase.evaluationMethodConfiguration.type : null;
            return evaluationType ? this.statusEvaluation[evaluationType] : null;
        },
        status () {
            const result = {};
            for (let status of this.statusDict) {
                result[status.value] = status.label;
            }
            return result;
        },
        categories (){
            if (this.phase.registrationCategories && this.phase.registrationCategories.length > 0) {
                const result = {};
                for (let category of this.phase.registrationCategories) {
                    result[category.replace(/,/g, '\\,')] = category;
                }
                return result;
            }
            return null;
        },
        proponentTypes (){
            if (this.phase.registrationProponentTypes && this.phase.registrationProponentTypes.length > 0) {
                const result = {};
                for (let proponentType of this.phase.registrationProponentTypes) {
                    result[proponentType.replace(/,/g, '\\,')] = proponentType;
                }
                return result;
            }
            return null;
        },
        ranges (){
            if (this.phase.registrationRanges && this.phase.registrationRanges.length > 0) {
                const result = {};
                for (let range of this.phase.registrationRanges) {
                    result[range.replace(/,/g, '\\,')] = range;
                }
                return result;
            }
            return null;
        },
        headers () {
            let itens = [
                { text: "Inscrição", value: "number" },
                { text: "Agente", value: "owner.name", slug: "agent"},
                ...this.avaliableFields.map((item) => { return {text: item.title, value: item.fieldName} }),
                { text: "Anexo", value: "attachments" },
                { text: "Status", value: "status"},
            ];

            if(this.phase.evaluationMethodConfiguration){
                itens.splice(2,0,{ text: "Avaliação", value: "consolidatedResult"});
            }

            itens.splice(3,0,{ text: "Pontuação", value: "score"});


            return itens;
        },
        select() {
            const fields = this.avaliableFields.map((item) => item.fieldName);
            
            return ['number,consolidatedResult,score,status,files,owner.{name,geoMesoregiao}', ...fields].join(',');
        },
        previousPhase() {
            const phases = $MAPAS.opportunityPhases;
            const index = phases.findIndex(item => item.__objectType == this.phase.__objectType && item.id == this.phase.id) - 1;
            return phases[index];
        },
    },

    methods: {
        setStatus(selected, entity) {
            entity.status = selected.value;
            entity.save();
        },

        clearFilters(entities) {
            this.selectedStatus = [];
            this.selectedCategories = [];
            this.selectedProponentTypes = [];
            this.selectedRanges = [];
            this.selectedAvaliation = null;
            delete this.query['range'];
            delete this.query['status'];
            delete this.query['category'];
            delete this.query['proponentType'];
            delete this.query['consolidatedResult'];
            entities.refresh();
        },

        removeFilter(filter) {
            switch (filter.prop) {
                case 'status':
                    this.selectedStatus = this.selectedStatus.filter(status => status !== filter.value);
                    break;
                case 'category':
                    this.selectedCategories = this.selectedCategories.filter(category => category !== filter.value);
                    break;
                case 'proponentType':
                    this.selectedProponentTypes = this.selectedProponentTypes.filter(proponentType => proponentType !== filter.value);
                    break;
                case 'range':
                    this.selectedRanges = this.selectedRanges.filter(range => range !== filter.value);
                    break;
            }
        },

        filterByStatus(entities) {
            this.query['status'] = `IN(${this.selectedStatus.toString()})`;
            entities.refresh();
        },
        
        filterByCategories(entities) {
            this.query['category'] = `IN(${this.selectedCategories.toString()})`;
            entities.refresh();
        },

        filterByProponentTypes(entities) {
            this.query['proponentType'] = `IN(${this.selectedProponentTypes.toString()})`;
            entities.refresh();
        },
        
        filterByRanges(entities) {
            this.query['range'] = `IN(${this.selectedRanges.toString()})`;
            entities.refresh();
        },

        filterAvaliation(option,entities){
            this.selectedAvaliation = option.value;
            this.query['consolidatedResult'] = `EQ(${this.selectedAvaliation})`;
            entities.refresh();
            
        },

        consolidatedResultToString(entity) {
            if(this.phase.evaluationMethodConfiguration){
                let type = this.phase.evaluationMethodConfiguration.type.id || this.phase.evaluationMethodConfiguration.type;
                if(type == "technical"){
                    return entity.consolidatedResult;
                }else{
                    return this.statusEvaluation[type][entity.consolidatedResult];
                }
            }
            return "";
        },
        
        statusToString(status) {
            return this.text(status)
        },

        isFuture() {
            const phase = this.phase;
            if (phase.isLastPhase) {
                const previousPhase = this.previousPhase;
                const date = previousPhase.evaluationTo || previousPhase.registrationTo;

                return date?.isFuture();
            } else {
                return phase.registrationFrom?.isFuture()
            }
        },

        isHappening() {
            return this.phase.registrationFrom?.isPast() && this.phase.registrationTo?.isFuture();
        },

        isPast() {
            const phase = this.phase;
            if (phase.isLastPhase) {
                return phase.publishTimestamp?.isPast();
            } else {
                return phase.registrationTo?.isPast();
            }
        }
    }
});