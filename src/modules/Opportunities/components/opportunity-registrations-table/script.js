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
        return { }
    },

    computed: {
        statusDict () {
            return $MAPAS.config.opportunityRegistrationTable;
        },
        headers () {
            return [
                { text: "Inscrição", value: "number" },
                { text: "Agente", value: "owner.name", slug: "agent"},
                { text: "Categoria", value: "category" },
                { text: "Status", value: "status"},
                { text: "Resultado final da avaliação", value: "consolidatedResult"},
                { text: "", value: "options"},
            ];
        },
        query() {
            return {'opportunity': `EQ(${this.phase.id})`}
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
        alterStatus(entity){
            entity.save();
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