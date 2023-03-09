app.component('opportunity-phase-config-evaluation' , {
    template: $TEMPLATES['opportunity-phase-config-evaluation'],

    setup() {
        const text = Utils.getTexts('opportunity-phase-config-evaluation');
        return { text };
    },

    data () {
        return {
            infos: {
                'general': '',
                'Música': '',
                'Dança': '',
                'Teatro': ''
            }
        };
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
        categories(){
            return this.entity.registrationCategories instanceof Array ?  this.entity.registrationCategories : [];
        }
    },

    methods: {
        getMinDate () {
            if(this.currentIndex === 0) {
                return null;
            }

            const previousPhase = this.phases[this.currentIndex - 1];

            if(previousPhase && previousPhase.__objectType === 'evaluationmethodconfiguration') {
                return previousPhase.registrationTo?._date || null;
            } else if(previousPhase && previousPhase.__objectType === 'opportunity') {
                return previousPhase.registrationFrom?._date || null;
            }
        },
        getMaxDate () {
            const nextPhase = this.phases[this.currentIndex + 1];
            const currentPhase = this.phases[this.currentIndex];

            if(nextPhase && nextPhase.__objectType === 'opportunity'){
                return nextPhase.registrationFrom?._date || null;
            }else if(nextPhase && nextPhase.__objectType === 'evaluationmethodconfiguration'){
                if(currentPhase && currentPhase.__objectType === 'evaluationmethodconfiguration'){
                    return nextPhase.evaluationFrom?._date || null;
                }
            }

        },
        async deletePhase (event, item, index) {
            const messages = useMessages();
            try {
                await item.destroy();
                this.phases.splice(index, 1);
            } catch (e) {
                messages.error(this.text('nao foi possivel remover fase'));
            }

        }
    }
});