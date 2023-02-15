app.component('opportunity-phases-config', {
    template: $TEMPLATES['opportunity-phases-config'],
    
    // define os eventos que este componente emite
    emits: ['newPhase', 'newDataCollectionPhase', 'newEvaluationPhase'],

    props: {
        entity: {
            type: Entity,
            required: true
        },
    },

    setup() { 
        // os textos estão localizados no arquivo texts.php deste componente 
        const text = Utils.getTexts('opportunity-phases-config')
        return { text }
    },

    async created() {
        const api = new OpportunitiesAPI();
        
        this.phases = await api.getPhases(this.entity.id);
    },

    data() {
        return {
            phases: [],
            evaluationMethods: $MAPAS.config.evaluationMethods
        }
    },

    computed: {
        phaseEvaluation () {
            return this.phases.find(item => item.__objectType === 'evaluationmethodconfiguration') || null;
        }
    },
    
    methods: {
        getMinDate (phase, index) {
            const previousPhase = this.getPreviousPhase(index);

            if(index === 0) {
                return undefined;
            }

            if (phase === 'evaluationmethodconfiguration') {
                if(previousPhase.__objectType === 'evaluationmethodconfiguration') {
                    console.log({ 'previousPhase.registrationTo': previousPhase.registrationTo });
                    return previousPhase.registrationTo._date;
                } else if(previousPhase.__objectType === 'opportunity') {
                    console.log({ 'previousPhase.registrationFrom': previousPhase.registrationFrom });
                    return previousPhase.registrationFrom._date;
                }
            } else if (phase === 'opportunity') {
                console.log({ 'previousPhase.registrationTo|previousPhase.evaluationTo': previousPhase.registrationTo || previousPhase.evaluationTo });
                return previousPhase.registrationTo._date || previousPhase.evaluationTo._date;
            }

        },
        getMaxDate (phase, index) {
            const lastPhase = this.getLastPhase();
            const nextPhase = this.getNextPhase(index);
            const currentPhase = this.phases[index];

            if(this.isLastPhase(index)) {
                console.log({ 'lastPhase.publishTimestamp': lastPhase.publishTimestamp });
                return lastPhase.publishTimestamp._date;
            }

            if(nextPhase.__objectType === 'opportunity') {
                console.log({ 'nextPhase.registrationFrom': nextPhase.registrationFrom });
                return nextPhase.registrationFrom._date;
            } else if(nextPhase.__objectType === 'evaluationmethodconfiguration') {
                if(currentPhase.__objectType === 'opportunity') {
                    console.log({ 'nextPhase.evaluationTo': nextPhase.evaluationTo });
                    return nextPhase.evaluationTo._date;
                } else if(currentPhase.__objectType === 'evaluationmethodconfiguration') {
                    console.log({ 'nextPhase.evaluationFrom': nextPhase.evaluationFrom });
                    return nextPhase.evaluationFrom._date;
                }
            }

        },
        getLastPhase () {
            if(this.phases[this.phases.length - 1]) {
                return this.phases[this.phases.length - 1];
            } else {
                return undefined;
            }
        },
        isLastPhase (index) {
            return index === this.phases.length - 1;
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
        }
    },
});
