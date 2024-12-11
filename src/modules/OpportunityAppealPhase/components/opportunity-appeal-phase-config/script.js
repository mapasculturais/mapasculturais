app.component('opportunity-appeal-phase-config' , {
    template: $TEMPLATES['opportunity-appeal-phase-config'],

    setup() {
        const text = Utils.getTexts('opportunity-appeal-phase-config');
        return { text };
    },

    props: {
        phase: {
            type: Entity,
            required: true
        },

        phases: {
            type: Array,
            required: true
        },
    },

    data() {
        // console.log(this.phase);
        return {
            processing: false,
            phaseData: {},
            entity: null,
            moreResponse: false,
            showButtonEvaluationCommittee: true, 
            trashButton: false,  
        }
    },


    mounted() { 
        
    },

    computed: {
        firstPhase() {
            return this.phases[0];
        },

        lastPhase() {
            const lastPhase = this.phases[this.phases.length - 1];
            if (lastPhase.isLastPhase) {
                return lastPhase;
            }
        },

        fromDateMin() {
            return this.phase.publishTimestamp || this.firstPhase.publishTimestamp;
        },

        fromDateMax() {
            return null;
        },

        toDateMin() {
            return this.phase.appealFrom || this.phase.publishTimestamp;
        },

        toDateMax() {
            return null;
        },

        appealFrom() {
            return this.entity.appealFrom
                ? this.entity.appealFrom.format({ day: '2-digit', month: '2-digit', year: 'numeric' })
                : '';
        },

        appealTo() {
            return this.entity.appealTo
                ? this.entity.appealTo.format({ day: '2-digit', month: '2-digit', year: 'numeric' })
                : '';
        },

        responseFrom() {
            return this.entity.responseFrom 
                ? this.entity.responseFrom.format({ day: '2-digit', month: '2-digit', year: 'numeric' })
                : '';
        },

        responseTo() {
            return this.entity.responseTo
                ? this.entity.responseTo.format({ day: '2-digit', month: '2-digit', year: 'numeric' })
                : '';
        },

    },

    methods: {
        createAppealPhase() {
            this.processing = true;
            const messages = useMessages();

            let args = {};
            this.phase.POST('createAppealPhase', args).then((data) => {
                this.phaseData = data;
                this.entity = new Entity('opportunity');
                this.entity.populate(this.phaseData);
                this.entity.type = this.phase.type;
                this.entity.save();
            
                // console.log(this.entity);
                this.processing = false;
                // messages.success(this.text('success'));
            }).catch((data) => {
                messages.error(data.error);
            });
        },

        addEvaluationCommittee() {
            this.showButtonEvaluationCommittee = false;
        },

    }
});