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
                { text: "Nome", value: "nome"},
                { text: "ID", value: "id"},
                { text: "cpf", value: "cpf"},
                { text: "status", value: "status"},
                { text: "Conferir Inscrição", value: "open"},
                { text: "Situação", value: "option"},

            ],
            items: [
                { nome: "Nometeste", id: 1, cpf: "089478383", status: 1 },
                { nome: "Nomedois", id: 2, cpf: "089455583", status: 10 },
                { nome: "Nomedois", id: 3, cpf: "089455583", status: 10, option: [2, 3, 4] },
            ],
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