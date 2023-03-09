app.component('opportunity-phase-config-data-collection' , {
    template: $TEMPLATES['opportunity-phase-config-data-collection'],

    setup() {
        const text = Utils.getTexts('opportunity-phase-config-data-collection');
        return { text };
    },

    data () {
        return {};
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

    computed: {
    },

    mounted () {
    },

    methods: {
        getMinDate (phase, index) {
            const previousPhase = this.getPreviousPhase(index);

            if(index === 0) {
                return undefined;
            }

            if (phase === 'opportunity') {
                return previousPhase.registrationTo?._date || previousPhase.evaluationTo?._date;
            } else if (phase === 'evaluationmethodconfiguration') {
                if(previousPhase.__objectType === 'evaluationmethodconfiguration') {
                    return previousPhase.registrationTo?._date;
                } else if(previousPhase.__objectType === 'opportunity') {
                    return previousPhase.registrationFrom?._date;
                }
            }
        },
        getMaxDate (phase, index) {
            const nextPhase = this.getNextPhase(index);
            const currentPhase = this.phases[index];

            if(nextPhase && nextPhase.__objectType === 'opportunity'){
                return nextPhase.registrationFrom._date;
            }else if(nextPhase && nextPhase.__objectType === 'evaluationmethodconfiguration'){
                if(currentPhase.__objectType === 'opportunity'){
                    return nextPhase.evaluationTo._date;
                }
                if(currentPhase.__objectType === 'evaluationmethodconfiguration'){
                    return nextPhase.evaluationFrom._date;
                }
            }

        },
        getPreviousPhase (currentIndex) {
            if(this.phases[currentIndex - 1]) {
                return this.phases[currentIndex - 1];
            } else {
                return undefined;
            }
        },
        getNextPhase (currentIndex) {
            if(this.phases[currentIndex + 1]) {
                return this.phases[currentIndex + 1];
            } else {
                return undefined;
            }
        },
        async deletePhase (event, item, index) {
            const messages = useMessages();
            try{
                await item.destroy();
                this.phases.splice(index, 1);
            }catch{
                messages.error(this.text('nao foi possivel remover fase'));
            }

        }
    }
});