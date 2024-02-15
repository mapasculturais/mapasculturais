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
        return {
            filters: {},
            resultStatus:[],
            query: {
                opportunity: `EQ(${this.phase.id})`,
                status: `GTE(0)`,
            },
            selectedCategory:null,
            selectedStatus:null,
            selectedStatus:null,
            selectedAvaliation:null
        }
    },

    computed: {
        statusDict () {
            return $MAPAS.config.opportunityRegistrationTable.registrationStatusDict;
        },
        statusEvaluation () {
            return $MAPAS.config.opportunityRegistrationTable.evaluationStatusDict;
        },
        statusEvaluationResult () {
            let evaluationType = this.phase.evaluationMethodConfiguration ? this.phase.evaluationMethodConfiguration.type : null;
            return evaluationType ? this.statusEvaluation[evaluationType] : null;
        },
        statusCategory (){
            return this.phase.registrationCategories;
        },
        headers () {
            let itens = [
                { text: "Inscrição", value: "number" },
                { text: "Categoria", value: "category" },
                { text: "Agente", value: "owner.name", slug: "agent"},
                { text: "Anexo", value: "attachments" },
                { text: "Status", value: "status"},
            ];

            if(this.phase.evaluationMethodConfiguration){
                itens.splice(3,0,{ text: "Resultado final da avaliação", value: "consolidatedResult"});
            }

            if(this.statusCategory.length == 0){
                let categoryInxed = itens.findIndex((item) => item.text === "Categoria");
                itens.splice(categoryInxed,1);
            }
            return itens;
        },
        select() {
            return "number,category,consolidatedResult,status,singleUrl,files,owner.{name}";
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
            this.selectedCategory = null;
            this.selectedStatus = null;
            this.selectedStatus = null;
            this.selectedAvaliation = null;
            this.query['status'] = `GTE(0)`;
            delete this.query['category'];
            delete this.query['consolidatedResult'];
            entities.refresh();
        },

        filterByCategory(option,entities) {
            this.selectedCategory = option.value;
            this.query['category'] = `EQ(${this.selectedCategory})`;
            entities.refresh();
        },
        filterByStatus(option,entities) {
            this.selectedStatus = option.value;
            this.query['status'] = `EQ(${this.selectedStatus})`;
            entities.refresh();
        },
        filterAvaliation(option,entities){
            this.selectedAvaliation = option.value;
            this.query['consolidatedResult'] = `EQ(${this.selectedAvaliation})`;
            entities.refresh();
            
        },

        consolidatedResultToString(entity) {
            if(this.phase.evaluationMethodConfiguration){
                let type = this.phase.evaluationMethodConfiguration.type;
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