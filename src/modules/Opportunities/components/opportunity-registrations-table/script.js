app.component('opportunity-registrations-table', {
    template: $TEMPLATES['opportunity-registrations-table'],
    props: {
        phase: {
            type: Entity,
            required: true
        },
        visibleColumns: {
            // F4 (#20): médias de nota visíveis por padrão (CA-11)
            type: Array,
            default: [
                "number",
                "agent",
                "consolidatedResult",
                "appliedPointReward",
                "score",
                "averageOriginalScore",
                "averageCorrectedScore",
                "scoreDifference",
                "status",
            ],
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
            label: text("Elegível para cotas"),
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
        
        const isAffirmativePoliciesActive = $MAPAS.config.opportunityRegistrationTable.isAffirmativePoliciesActive;
        const hadTechnicalEvaluationPhase = $MAPAS.config.opportunityRegistrationTable.hadTechnicalEvaluationPhase;
        const isTechnicalEvaluationPhase = $MAPAS.config.opportunityRegistrationTable.isTechnicalEvaluationPhase;
        
        const defaultHeaders = $MAPAS.config.opportunityRegistrationTable.defaultHeaders;
        const default_select = $MAPAS.config.opportunityRegistrationTable.defaultSelect;
        const defaultAvailable = $MAPAS.config.opportunityRegistrationTable.defaultAvailable;
        
        let avaliableFields = defaultAvailable.length > 0 ? [...defaultAvailable] : [];
        let visible = this.visibleColumns.join(',');
        let order = 'status DESC,consolidatedResult DESC';
        let consolidatedResultOrder = 'consolidatedResult';

        /*
            F1 (#17) — override 2026-09-08 (épica #7): a coluna vive na lista
            de inscritos da FASE DE RECURSO. O contexto (appealContexts) é
            injetado pelo init.php do componente
            opportunity-appeal-correction-assignment SOMENTE quando a fase em
            contexto é a própria fase de recurso ativa, com fase pai técnica,
            e o usuário tem @control na fase pai (gates espelhados de
            RegistrationAppealReview::eligibleCorrectors()) — a presença da
            chave já carrega a checagem de permissão.
        */
        const appeal_correction_config = $MAPAS.config.appealCorrectionAssignment || {};
        const appeal_context = appeal_correction_config.appealContexts?.[this.phase.id] ?? null;
        const appeal_correction_enabled = appeal_context != null;

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

        const sortedAvaliableFields = [...avaliableFields];
        const steppedFields = sortedAvaliableFields.filter(item => item.step !== undefined);

        // Ordena por step.displayOrder e depois por displayOrder
        const sortedElements = [...steppedFields].sort((a, b) => {
            const stepSortKeyA = a.step?.displayOrder ?? Infinity;
            const stepSortKeyB = b.step?.displayOrder ?? Infinity;
            
        
            if (stepSortKeyA !== stepSortKeyB) {
                return stepSortKeyA - stepSortKeyB;
            }
        
            const displayOrderA = a.displayOrder ?? Infinity;
            const displayOrderB = b.displayOrder ?? Infinity;
        
            return displayOrderA - displayOrderB;
        });
        
        // Preenche de volta no array original mantendo os elementos que não têm step/displayOrder nas mesmas posições
        let sortedIndex = 0;
        for (let i = 0; i < sortedAvaliableFields.length; i++) {
            if (sortedAvaliableFields[i].step !== undefined) {
                sortedAvaliableFields[i] = sortedElements[sortedIndex++];
            }
        }

        avaliableFields = sortedAvaliableFields;

        if(isTechnicalEvaluationPhase){
            consolidatedResultOrder = 'consolidatedResult AS FLOAT';
        }

        const sortOptions = [
            { value: 'sentTimestamp ASC', label: this.text('enviadas há mais tempo primeiro') },
            { value: 'sentTimestamp DESC', label: this.text('enviadas há menos tempo primeiro') },
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
            
            // Exibe por padrão as faixas/linhas quando as mesmas existe e estão configuradas
            if(this.phase.registrationRanges && this.phase.registrationRanges.length > 0) {
                visible += ',range';
            }
        }

        // F1 (#17): coluna de designação de correção visível apenas no contexto elegível
        if(appeal_correction_enabled) {
            visible += ',appealCorrection';
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
            appealCorrectionEnabled: appeal_correction_enabled,
            appealMainPhaseId: appeal_context?.mainPhaseId ?? null,
            appealMainRegistrations: {},
            isAffirmativePoliciesActive,
            hadTechnicalEvaluationPhase,
            isTechnicalEvaluationPhase,
            defaultHeaders,
            default_select,
            downloadZipInLoandingEntity: null
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
            let evaluationType = this.phase.evaluationMethodConfiguration ? this.phase.evaluationMethodConfiguration.type?.id : null;
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
                    if (!category) {
                        continue;
                    }

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
        hasEvaluationMethodTechnical (){
            const type = this.phase.evaluationMethodConfiguration?.type?.id;
            const phases = $MAPAS.opportunityPhases;
            let result = false;

            for (const phase of phases){
                if(phase.id == this.phase.id){
                    break;
                }

                const phaseType = phase.evaluationMethodConfiguration ? phase.evaluationMethodConfiguration.type?.id : phase.type.id;

                if(phaseType == "technical"){
                    result = true;
                    break;
                }
            }

            return result;
        },
        headers () {
            let itens = this.defaultHeaders;

            const agentIndex = itens.findIndex(item => item.slug === 'agent');

            if (agentIndex !== -1) {
                itens.splice(agentIndex + 1, 0, ...this.avaliableFields.map(item => ({
                    text: item.title,
                    value: item.fieldName
                })));
            }
            

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
                itens.push({ text: __('Resultado final', 'opportunity-registrations-table'), value: "status", width: '250px', stickyRight: true})
            } else {
                itens.push({ text: __('status', 'opportunity-registrations-table'), value: "status", width: '250px', stickyRight: true})
            }

            // F1 (#17): coluna de designação de correção (recurso deferido),
            // imediatamente antes da coluna de status (sticky right). A coluna
            // também pode ser exposta no contexto de resultados pelo hook
            // component(opportunity-results-table).visibleColumns.
            if(this.appealCorrectionEnabled && !itens.some(item => item.value === 'appealCorrection')) {
                itens.splice(itens.length - 1, 0, {
                    text: __('Designar correção', 'opportunity-registrations-table'),
                    value: 'appealCorrection',
                });
            }

            // Pontuação final (score = avaliação + bônus). Exibir na fase técnica,
            // em fases posteriores a uma técnica, na publicação do resultado, ou
            // quando o edital tem (ou terá) fase técnica — senão o default de
            // sistema (Avaliação/Bônus/Pontuação) some na lista da 1ª fase.
            if(this.shouldShowScoreColumn) {
                if(!itens.some(item => item.value === 'score')) {
                    itens.push({
                        text: this.text('Pontuação'),
                        value: 'score',
                    });
                }
            } else {
                itens = itens.filter(item => !['score', 'appliedPointReward'].includes(item.value));
            }

            // Mantém Avaliação → Bônus → Pontuação sempre lado a lado
            itens = this.groupScoreRelatedColumns(itens);

            // eligible só faz sentido com políticas afirmativas / contexto técnico
            if(!(this.isTechnicalEvaluationPhase || this.hasEvaluationMethodTechnical)) {
                itens = itens.filter(item => item.value !== 'eligible');
            }

            if(this.avaliableColumns) {
                itens = itens.filter((item) => {
                    return this.avaliableColumns.indexOf(item.value) >= 0;
                });
            }

            return itens;
        },
        /**
         * True se qualquer fase do edital for avaliação técnica (incluindo
         * fases futuras). hadTechnicalEvaluationPhase / hasEvaluationMethodTechnical
         * só olham para trás — na 1ª fase isso escondia Pontuação mesmo com
         * fase técnica no edital (ex.: /lista-de-inscricoes/98/ com fase 107).
         */
        opportunityHasTechnicalEvaluation() {
            const phases = $MAPAS.opportunityPhases || [];
            return phases.some((phase) => {
                const typeId = phase.evaluationMethodConfiguration?.type?.id
                    ?? phase.type?.id
                    ?? phase.type;
                return typeId === 'technical';
            });
        },
        shouldShowScoreColumn() {
            return !!(
                this.isTechnicalEvaluationPhase
                || this.hasEvaluationMethodTechnical
                || this.hadTechnicalEvaluationPhase
                || this.phase.isLastPhase
                || this.opportunityHasTechnicalEvaluation
            );
        },
        select() {
            let avaliableFields = this.avaliableFields.map((item) => item.fieldName);
            
            if(this.isTechnicalEvaluationPhase) {
                avaliableFields.push('usingQuota')
                avaliableFields.push('quotas')
                avaliableFields.push('tiebreaker')
            }

            let fields = [...this.default_select.split(','), ...avaliableFields];

            if(this.shouldShowScoreColumn && !fields.includes('score')) {
                fields.push('score');
            }
            if(this.shouldShowScoreColumn && !fields.includes('appliedPointReward')) {
                fields.push('appliedPointReward');
            }

            if(!this.shouldShowScoreColumn) {
                fields = fields.filter(item => item !== 'score' && item !== 'appliedPointReward');
            }

            if(!(this.isTechnicalEvaluationPhase || this.hasEvaluationMethodTechnical)) {
                fields = fields.filter(item => item !== 'eligible');
            }
            
            return fields.join(',');

        },
        previousPhase() {
            const phases = $MAPAS.opportunityPhases;
            const index = phases.findIndex(item => item.__objectType == this.phase.__objectType && item.id == this.phase.id) - 1;
            return phases[index];
        },
    },

    methods: {
        /**
         * Pipeline das linhas da lista de inscritos.
         *
         * F4 (#20): as colunas computadas de nota (averageOriginalScore,
         * averageCorrectedScore, scoreDifference) vêm da API (hook
         * ApiQuery(registration).findResult do OpportunityAppealPhase), mas o
         * Entity.populate() do SDK DESCARTA propriedades fora de
         * $PROPERTIES/$RELATIONS — com o pipeline padrão do mc-entities as
         * chaves morriam entre a resposta HTTP e o render (célula "-").
         * Com rawProcessor o fetch vira raw (mc-entities/script.js:137-140)
         * e este método povoa a entidade e copia as chaves computadas da
         * resposta bruta.
         */
        rawProcessor(rawData) {
            const registrationApi = new API('registration');
            const registration = registrationApi.getEntityInstance(rawData.id);
            registration.populate(rawData, true);

            registration.averageOriginalScore = rawData.averageOriginalScore ?? null;
            registration.averageCorrectedScore = rawData.averageCorrectedScore ?? null;
            registration.scoreDifference = rawData.scoreDifference ?? null;

            return registration;
        },

        /**
         * Agrupa Avaliação → Bônus por pontuação → Pontuação (nota final).
         */
        groupScoreRelatedColumns(itens) {
            const groupOrder = ['consolidatedResult', 'appliedPointReward', 'score'];
            const groupItems = [];
            const rest = [];

            for (const item of itens) {
                const key = item.value || item.slug;
                if (groupOrder.includes(key)) {
                    groupItems.push(item);
                } else {
                    rest.push(item);
                }
            }

            if (!groupItems.length) {
                return itens;
            }

            const byKey = {};
            for (const item of groupItems) {
                byKey[item.value || item.slug] = item;
            }
            const orderedGroup = groupOrder.map(key => byKey[key]).filter(Boolean);

            // Insere o bloco na posição da primeira coluna do grupo que existia
            let insertAt = itens.findIndex(item => groupOrder.includes(item.value || item.slug));
            if (insertAt < 0) {
                insertAt = Math.min(2, rest.length);
            }
            // Ajusta insertAt para o índice equivalente em `rest`
            let restInsertAt = 0;
            let seen = 0;
            for (let i = 0; i < itens.length && seen < insertAt; i++) {
                const key = itens[i].value || itens[i].slug;
                if (!groupOrder.includes(key)) {
                    restInsertAt++;
                }
                seen++;
            }

            return [
                ...rest.slice(0, restInsertAt),
                ...orderedGroup,
                ...rest.slice(restInsertAt),
            ];
        },

        getStatus(actualStatus) {
            return this.statusDict.find(status => status.value === actualStatus);
        },

        // F4 (#20): colunas de média/nota — null vira "-", números sem zeros à direita.
        formatScoreColumn(value) {
            if (value === null || value === undefined) {
                return '-';
            }

            const number = Number(value);

            return Number.isFinite(number) ? String(parseFloat(number.toFixed(2))) : '-';
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
                let type = this.phase.evaluationMethodConfiguration?.type?.id || this.phase.evaluationMethodConfiguration?.type;
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
            if (phase.parent?.isContinuousFlow) {
                return false;
            }
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
        },
        downloadZipInLoanding(entity) {
            if(entity.id == this.downloadZipInLoandingEntity) {
                return true;
            }

            return false;
        },
        generateOrDownloadZip(entity) {
            const apiUrl = Utils.createUrl('registration', 'createZipFiles', { id: entity.id });
            window.open(apiUrl, '_blank');
        },

        /**
         * F1 (#17) — override 2026-09-08: o botão vive na linha do recurso
         * com status Deferido (10) da lista da fase de recurso. Abre o modal
         * de designação (F2) pela interface pública documentada no init.php
         * do componente opportunity-appeal-correction-assignment, com
         * {opportunity: fase PRINCIPAL, registration: inscrição da fase
         * PRINCIPAL} — ambos derivados da linha da fase de recurso. O modal
         * decide o que exibir (designação ou acompanhamento).
         */
        async openAppealCorrectionAssignment(entity) {
            const main_registration = await this.findMainPhaseRegistration(entity);

            if (!main_registration) {
                return;
            }

            window.dispatchEvent(new CustomEvent('opportunity-appeal-correction-assignment:open', {
                detail: {
                    opportunity: { id: this.appealMainPhaseId },
                    registration: { id: main_registration.id, number: main_registration.number },
                },
            }));
        },

        /**
         * F1 (#17): deriva a inscrição da fase principal a partir da
         * inscrição da fase de recurso. A inscrição de recurso herda o
         * `number` da inscrição principal (createAppealPhaseRegistration,
         * OpportunityAppealPhase/Module.php:201) e não existe meta/relation
         * armazenada ligando as duas — o casamento por `number` na fase pai
         * é o mecanismo canônico do backend (Module.php:240-243). Uma
         * chamada por inscrição, cacheada para cliques repetidos.
         */
        async findMainPhaseRegistration(appealRegistration) {
            const appeal_registration_id = appealRegistration?.id;

            if (!appeal_registration_id || !this.appealMainPhaseId) {
                return null;
            }

            if (this.appealMainRegistrations[appeal_registration_id]) {
                return this.appealMainRegistrations[appeal_registration_id];
            }

            const api = new API('registration');

            try {
                const found = await api.find({
                    'opportunity': `EQ(${this.appealMainPhaseId})`,
                    'number': `EQ(${appealRegistration.number})`,
                    '@select': 'id,number',
                    '@permissions': 'view',
                });

                const main_registration = (found || [])[0] || null;

                if (!main_registration) {
                    throw new Error(`inscrição da fase principal não encontrada (number=${appealRegistration.number})`);
                }

                this.appealMainRegistrations[appeal_registration_id] = main_registration;

                return main_registration;
            } catch (error) {
                console.error('opportunity-registrations-table:findMainPhaseRegistration', error);
                this.messages.error(this.text('inscrição da fase principal não encontrada'));

                return null;
            }
        }
    }
});
