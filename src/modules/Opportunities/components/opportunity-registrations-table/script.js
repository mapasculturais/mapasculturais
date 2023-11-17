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
            headers: [

                { text: "Inscrição", value: "registration" },
                { text: "Categoria", value: "category" },
                { text: "Agente", value: "agent" },
                // { text: "Resultado final das avaliações", value: "evaluation" },
                { text: "status", value: "status" },
                // { text: "Situação", value: "" },
                { text: "Conferir Inscrição", value: "open" },

            ],
            items: this.registrationsItems(),

        }
    },

    computed: {
        previousPhase() {
            const phases = $MAPAS.opportunityPhases;
            const index = phases.findIndex(item => item.__objectType == this.phase.__objectType && item.id == this.phase.id) - 1;
            return phases[index];
        },

    },

    methods: {
        registrationsItems() {
            const registrations = $MAPAS.config.opportunityRegistrationsTable.registrations;
            let result = []
            console.log(registrations);
            registrations.forEach(registration => {
                let item = {
                    'registration': registration.number,
                    'category': registration.category,
                    'agent': registration.owner.name,
                    'status': registration.status,

                };
                result.push(item);
            });
            console.log(result);
            return result;
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