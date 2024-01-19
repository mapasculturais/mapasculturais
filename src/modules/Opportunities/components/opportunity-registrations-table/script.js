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
            let evaluationType =  this.phase.evaluationMethodConfiguration.type;
            return this.statusEvaluation[evaluationType];
        },
        statusCategory (){
            return this.phase.registrationCategories;
        },
        headers () {
            let itens = [
                { text: "Inscrição", value: "number" },
                { text: "Categoria", value: "category" },
                { text: "Agente", value: "owner.name", slug: "agent"},
                { text: "Status", value: "status"},
                { text: "", value: "options"},
            ];
            if(this.phase.evaluationMethodConfiguration){
                itens.splice(3,0,{ text: "Resultado final da avaliação", value: "consolidatedResult"});
            }
            return itens;
        },
        select() {
            return "number,category,consolidatedResult,status,singleUrl,owner.{name}";
        },
        previousPhase() {
            const phases = $MAPAS.opportunityPhases;
            const index = phases.findIndex(item => item.__objectType == this.phase.__objectType && item.id == this.phase.id) - 1;
            return phases[index];
        },
    },

    methods: {
        alterStatus(entity) {
            entity.save();
        },

        clearFilters(entities) {
            this.selectedCategory = null,
            this.selectedStatus = null,
            this.selectedStatus = null,
            this.selectedAvaliation = null
            delete this.query['category'];
            delete this.query['status'];
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