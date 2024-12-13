app.component('opportunity-registrations-table', {
    template: $TEMPLATES['opportunity-registrations-table'],
    props: {
        phase: {
            type: Entity,
            required: true
        },
        visibleColumns: {
            type: Array,
            default: ["agent", "status", "category", "consolidatedResult", "score", "editable"],
        },
        identifier: {
            type: String,
            required: true,
        },
        avaliableColumns: Array,
        hideFilters: Boolean,
        hideSort: Boolean,
        hideActions: Boolean,
        hideTitle: Boolean,
        hideHeader: Boolean,
        statusNotEditable: Boolean,
    },
    setup() {
        // os textos estão localizados no arquivo texts.php deste componente
        const messages = useMessages();
        const text = Utils.getTexts('opportunity-registrations-table');

        /* 
            adiciona a definição de quotas, tiebreaker e region, 
            que são retornados pela api mas nào são metadados, 
            possibilitando a utilização na tabela 
        */

        $DESCRIPTIONS.registration['quotas'] = {
            isMetadata: false,
            isEntityRelation: false,
            required: false,
            readonly: true,
            type: "array",
            length: 255,
            label: text("Elegível para as cotas"),
            isPK: false
        };

        $DESCRIPTIONS.registration['usingQuota'] = {
            isMetadata: false,
            isEntityRelation: false,
            required: false,
            readonly: true,
            type: "array",
            length: 255,
            label: text("Cotas aplicadas"),
            isPK: false
        };

        $DESCRIPTIONS.registration['tiebreaker'] = {
            isMetadata: false,
            isEntityRelation: false,
            required: false,
            readonly: true,
            type: "object",
            length: 255,
            label: text("Critérios de desempate"),
            isPK: false
        };

        $DESCRIPTIONS.registration['region'] = {
            isMetadata: false,
            isEntityRelation: false,
            required: false,
            readonly: true,
            type: "string",
            length: 255,
            label: text("Região"),
            isPK: false
        };

        return { messages, text }
    },
    data() {
        const $DESC = $DESCRIPTIONS.registration;
        const avaliableFields = [];

        const isAffirmativePoliciesActive = $MAPAS.config.opportunityRegistrationTable.isAffirmativePoliciesActive;
        const hadTechnicalEvaluationPhase = $MAPAS.config.opportunityRegistrationTable.hadTechnicalEvaluationPhase;
        const isTechnicalEvaluationPhase = $MAPAS.config.opportunityRegistrationTable.isTechnicalEvaluationPhase;
        
        let visible = this.visibleColumns.join(',');
        let order = 'status DESC,consolidatedResult DESC';
        let consolidatedResultOrder = 'consolidatedResult';

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

        if(isTechnicalEvaluationPhase){
            consolidatedResultOrder = 'consolidatedResult AS FLOAT';
        }

        const sortOptions = [
            { value: 'sentTimestamp ASC', label: this.text('enviadas a mais tempo primeiro') },
            { value: 'sentTimestamp DESC', label: this.text('enviadas a menos tempo primeiro') },
        ];

        if(this.phase.isLastPhase) {
            order = `status DESC,score DESC`;
            sortOptions.splice(0, 0, {value: 'score DESC,status DESC', label: this.text('pontuação final')});
            sortOptions.splice(0, 0, { value: `status ASC,score ASC`, label: this.text('status ascendente' )});
            sortOptions.splice(0, 0, { value: `status DESC,score DESC`, label: this.text('status descendente' )});

        } else { 
            sortOptions.splice(0, 0, { value: `${consolidatedResultOrder} DESC`, label: this.text('resultado das avaliações' )});
            sortOptions.splice(0, 0, { value: `status ASC,${consolidatedResultOrder} ASC`, label: this.text('status ascendente' )});
            sortOptions.splice(0, 0, { value: `status DESC,${consolidatedResultOrder} DESC`, label: this.text('status descendente' )});

            if(hadTechnicalEvaluationPhase) {
                order = 'score DESC,status DESC';
                sortOptions.splice(0, 0, {value: 'score DESC', label: this.text('pontuação final')});
            }
            
            if(isAffirmativePoliciesActive) {
                avaliableFields.splice(0,0, {
                    title: __('concorrendo por cota', 'opportunity-registrations-table'),
                    fieldName: 'eligible',
                    fieldType: 'boolean'
                });

                visible += ',eligible';
                if(isTechnicalEvaluationPhase) {
                    order = '@quota';
                    sortOptions.splice(0, 0, {value: '@quota', label: this.text('classificação final')});
                }
            }
        }
        
        return {
            sortOptions,
            filters: {},
            resultStatus:[],
            query: {
                '@opportunity': this.phase.id,
                'status': 'GTE(0)',
                '@permission': 'view'
            },
            selectedCategories: [],
            selectedProponentTypes: [],
            selectedRanges: [],
            selectedStatus: [],
            selectedAvaliation:null,
            order,
            avaliableFields,
            visible,
            isAffirmativePoliciesActive,
            hadTechnicalEvaluationPhase,
            isTechnicalEvaluationPhase
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
                    result[range.label.replace(/,/g, '\\,')] = range.label;
                }
                return result;
            }
            return null;
        },
        headers () {
            let itens = [
                { text: __('inscrição', 'opportunity-registrations-table'), value: "number", sticky: true, width: '160px' },
                { text: __('agente', 'opportunity-registrations-table'), value: "owner?.name", slug: "agent"},
                ...this.avaliableFields.map((item) => { return {text: item.title, value: item.fieldName} }),
                { text: __('anexos', 'opportunity-registrations-table'), value: "attachments" },
                { text: __('data de criação', 'opportunity-registrations-table'), value: "createTimestamp" },
                { text: __('data de envio', 'opportunity-registrations-table'), value: "sentTimestamp" },
                { text: __('Editavel para o proponente', 'opportunity-registrations-table'), slug: "editable"}
            ];

            if(this.phase.evaluationMethodConfiguration){
                itens.splice(2,0,{ text: "Avaliação", value: "consolidatedResult"});

                if(this.isTechnicalEvaluationPhase) {
                    const evaluationMethodConfiguration = this.phase.evaluationMethodConfiguration || {};
                    const tiebreakerConfiguration = evaluationMethodConfiguration.tiebreakerCriteriaConfiguration || [];
                    const quotaConfiguration = evaluationMethodConfiguration.quotaConfiguration || {};
                    const geoQuotaConfiguration = evaluationMethodConfiguration.geoQuotaConfiguration || {};
                    
                    if(tiebreakerConfiguration?.length > 0) {
                        itens.splice(3,0,{
                            text: __('Critérios de desempate', 'opportunity-registrations-table'),
                            value: 'tiebreaker',
                        });
                    }
        
                    if(quotaConfiguration.rules?.length > 0) {
                        itens.splice(5,0,{
                            text: __('Elegível para cotas', 'opportunity-registrations-table'),
                            value: 'quotas',
                        });

                        itens.splice(6,0,{
                            text: __('Cotas aplicadas', 'opportunity-registrations-table'),
                            value: 'usingQuota',
                        });
                    }
        
                    if(geoQuotaConfiguration?.geoDivision) {
                        itens.splice(7,0,{
                            text: __('Região', 'opportunity-registrations-table'),
                            value: 'region',
                        });
                    }
                }
            }

            if(this.phase.isLastPhase){
                itens.splice(2,0,{ text: "Status", value: "consolidatedResult"});
                itens.push({ text: __('resultado final', 'opportunity-registrations-table'), value: "status", width: '250px', stickyRight: true})
            } else {
                itens.push({ text: __('status', 'opportunity-registrations-table'), value: "status", width: '250px', stickyRight: true})
            }

            itens.splice(3,0,{ text: "Pontuação", value: "score"});

            if(this.avaliableColumns) {
                itens = itens.filter((item) => {
                    return this.avaliableColumns.indexOf(item.value) >= 0;
                });
            }


            return itens;
        },
        select() {
            const fields = this.avaliableFields.map((item) => item.fieldName);
            
            return ['number,consolidatedResult,score,status,sentTimestamp,createTimestamp,files,owner.{name,geoMesoregiao},editSentTimestamp,editableUntil,editableFields', ...fields].join(',');
        },
        previousPhase() {
            const phases = $MAPAS.opportunityPhases;
            const index = phases.findIndex(item => item.__objectType == this.phase.__objectType && item.id == this.phase.id) - 1;
            return phases[index];
        },
    },

    methods: {
        getStatus(actualStatus) {
            return this.statusDict.find(status => status.value === actualStatus);
        },

        setStatus(selected, entity) {
            const api = new API();
            const url = Utils.createUrl('registration', 'setStatusTo', {id: entity.id});
            api.POST(url, {status: selected.value}).then(res => res.json()).then(response => {
                if(response.error) {
                    this.messages.error(this.text(response.data))
                } else {
                    this.messages.success(this.text('status alterado com sucesso'))
                }
            });
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
            if (this.selectedStatus.length > 0) {
                this.query['status'] = `IN(${this.selectedStatus.toString()})`;
            } else {
                delete this.query['status'];
            }
            entities.refresh();
        },
        
        filterByCategories(entities) {
            if (this.selectedCategories.length > 0) {
                this.query['category'] = `IN(${this.selectedCategories.toString()})`;
            } else {
                delete this.query['category'];
            }
            entities.refresh();
        },

        filterByProponentTypes(entities) {
            if (this.selectedProponentTypes.length > 0) {
                this.query['proponentType'] = `IN(${this.selectedProponentTypes.toString()})`;
            } else {
                delete this.query['proponentType'];
            }
            entities.refresh();
        },
        
        filterByRanges(entities) {
            if (this.selectedRanges.length > 0) {
                this.query['range'] = `IN(${this.selectedRanges.toString()})`;
            } else {
                delete this.query['range'];
            }
            entities.refresh();
        },

        filterAvaliation(option,entities){
            this.selectedAvaliation = option.value;
            if (this.selectedAvaliation.length > 0) {
                this.query['consolidatedResult'] = `IN(${this.selectedAvaliation.toString()})`;
            } else {
                delete this.query['consolidatedResult'];
            }
            entities.refresh();
            
        },

        consolidatedResultToString(entity) {
            if(entity.consolidatedResult == '@tiebreaker') {
                return this.text('aguardando desempate');
            }

            if(this.phase.evaluationMethodConfiguration){
                let type = this.phase.evaluationMethodConfiguration.type.id || this.phase.evaluationMethodConfiguration.type;
                if(type == "technical"){
                    return entity.consolidatedResult;
                }else{
                    return this.statusEvaluation[type][entity.consolidatedResult];
                }
            } else if (this.phase.isLastPhase) {
                return entity.consolidatedResult;
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
        },


        statusEditRegistration(registration) {
            let editableUntil = registration.editableUntil ?? null;
            let editSentTimestamp = registration.editSentTimestamp ?? null;

            if(this.phase.registrationTo?.isFuture() && registration.status === 0) {
                return false;
            }

            if(this.phase.registrationTo?.isPast() && registration.status === 0) {
                return 'notEditable';
            }

            if (!editableUntil) {
                return 'editable';
            }

            if (!editSentTimestamp && editableUntil.isFuture()) {
                return 'open';
            }

            if (registration.editableFields && editSentTimestamp) {
                return 'sent';
            }

            if (!editSentTimestamp && editableUntil.isPast()) {
                return 'missed';
            }
        }
    }
});