app.component('opportunity-create-evaluation-phase' , {
    template: $TEMPLATES['opportunity-create-evaluation-phase'],
    emits: ['create'],

    data () {
        return {
            phase: null,
            dateStart: '',
            dateEnd: ''
        };
    },

    props: {
        opportunity: {
            type: Entity,
            required: true
        },
        previousPhase: {
            type: Entity,
            required: true
        },
        lastPhase: {
            type:Entity,
            required: true
        }
    },

    computed: {
        placeholder() {
            const labels = $DESCRIPTIONS.evaluationmethodconfiguration.type.options;

            return this.phase.type ? labels[this.phase.type] : '';
        },

        maxDate () {
            return this.lastPhase.publishTimestamp?._date || null;
        },

        minDate() {
            return this.previousPhase.registrationFrom?._date || this.previousPhase.evaluationFrom?._date;

            if (this.previousPhase.__objectType == 'evaluationmethodconfiguration') {
                // fase anterior é uma fase de avaliação
                return this.previousPhase.evaluationTo;
            } else {
                // fase anterior é uma fase de coleta de dados
                return this.previousPhase.registrationFrom;
            }
        },

        isContinuousFlow() {
            return this.opportunity?.isContinuousFlow;
        }
    },

    methods: {

        createEntity() {
            this.phase = Vue.ref(new Entity('evaluationmethodconfiguration'));
            this.phase.infos = {general: ''};
            this.phase.opportunity = this.opportunity;
            
            if(this.isContinuousFlow) {
                this.phase.evaluationFrom = this.opportunity.registrationFrom;
                this.phase.evaluationTo = this.opportunity?.hasEndDate ? this.lastPhase.publishTimestamp : this.opportunity.registrationTo;
                this.phase.publishedRegistrations = this.opportunity?.hasEndDate ? false : true;
            }
        },
        destroyEntity() {
            // para o conteúdo da modal não sumir antes dela fechar
            setTimeout(() => this.entity = null, 200);
        },
        async save(modal) {
            modal.loading(true);
            try{
                await this.phase.save();
                this.$emit('create', this.phase);
                modal.close();
                modal.loading(false);
            
            } catch(e) {
                modal.loading(false);
            }
        },
    }
});