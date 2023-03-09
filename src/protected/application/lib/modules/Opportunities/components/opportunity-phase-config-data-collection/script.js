app.component('opportunity-phase-config-data-collection' , {
    template: $TEMPLATES['opportunity-phase-config-data-collection'],

    setup() {
        const text = Utils.getTexts('opportunity-phase-config-data-collection');
        return { text };
    },

    props: {
        currentIndex: {
            type: Number,
            required: true
        },
        entity: {
            type: Entity,
            required: true
        },
        phases: {
            type: Array,
            required: true
        }
    },

    methods: {
        getMinDate () {
            if(this.currentIndex === 0) {
                return null;
            }

            const previousPhase = this.phases[this.currentIndex - 1];
            return previousPhase.registrationTo?._date || previousPhase.evaluationTo?._date;
        },
        getMaxDate () {
            const nextPhase = this.phases[this.currentIndex + 1];
            const currentPhase = this.phases[this.currentIndex];

            if(nextPhase && nextPhase.__objectType === 'opportunity'){
                return nextPhase.registrationFrom?._date || null;
            }else if(nextPhase && nextPhase.__objectType === 'evaluationmethodconfiguration'){
                if(currentPhase && currentPhase.__objectType === 'opportunity'){
                    return nextPhase.evaluationTo?._date || null;
                }
            }
        },
        async deletePhase (event, item, index) {
            const messages = useMessages();
            try{
                await item.destroy();
                this.phases.splice(index, 1);
            } catch (e) {
                messages.error(this.text('nao foi possivel remover fase'));
            }
        }
    }
});