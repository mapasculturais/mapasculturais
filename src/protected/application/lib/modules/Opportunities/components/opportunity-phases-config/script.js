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
            evaluationMethods: $MAPAS.config.evaluationMethods,
            infos: {
                'general': '',
                'Música': '',
                'Dança': '',
                'Teatro': ''
            }
        }
    },

    computed: {
        phaseEvaluation () {
            return this.phases.find(item => item.__objectType === 'evaluationmethodconfiguration') || null;
        },
        maxDateLastPhase () {
            return this.getLastPhase().registrationTo?._date ?? this.getLastPhase().evaluationTo?._date ?? '';
        },
        categories(){
            return this.entity.registrationCategories instanceof Array ?  this.entity.registrationCategories : [];
        }
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
            const lastPhase = this.getLastPhase();
            const nextPhase = this.getNextPhase(index);
            const currentPhase = this.phases[index];

            if(this.isLastPhase(index)) {
                return lastPhase.publishTimestamp._date;
            }

            if(nextPhase.__objectType === 'opportunity') {
                return nextPhase.registrationFrom._date;
            } else if(nextPhase.__objectType === 'evaluationmethodconfiguration') {
                if(currentPhase.__objectType === 'opportunity') {
                    return nextPhase.evaluationTo._date;
                } else if(currentPhase.__objectType === 'evaluationmethodconfiguration') {
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
        },
        addInPhases (phase) {
            console.log(phase)
            this.phases.splice(this.phases.length - 1, 0, phase);
            console.log(this.phases)
        }
    },
});
