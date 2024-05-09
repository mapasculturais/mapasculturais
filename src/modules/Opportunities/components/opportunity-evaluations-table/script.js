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
        }
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
    },
    
    methods: {
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

            console.log('----------------------------');
            console.log(rawData);
            console.log(registration);

            return registration;
        },

        getStatus(status) {
            switch(status) {
                case 0:
                    return  __('iniciada', 'opportunity-evaluations-table');
                case 1:
                    return  __('avaliada', 'opportunity-evaluations-table');
                case 2:
                    return  __('enviada', 'opportunity-evaluations-table');
                default:
                    return  __('pendente', 'opportunity-evaluations-table');
            }
        },

        getResultString(result) {
            return result ?? __('não avaliado', 'opportunity-evaluations-table');
        }
    }
});