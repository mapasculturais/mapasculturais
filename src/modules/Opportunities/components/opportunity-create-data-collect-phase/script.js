app.component('opportunity-create-data-collect-phase' , {
    template: $TEMPLATES['opportunity-create-data-collect-phase'],
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
        previousPhase:{
            type: Entity,
            required: true
        },
        lastPhase:{
            type:Entity,
            required: true
        }
    },

    computed: {
        maxDate () {
            return this.lastPhase.publishTimestamp?._date || null;
        },
        minDate () {
            return this.previousPhase.registrationFrom?._date || this.previousPhase.evaluationFrom?._date;
        }
    },

    methods: {

        createEntity() {
            this.phase = Vue.ref(new Entity('opportunity'));
            this.phase.ownerEntity = this.opportunity.ownerEntity;
            this.phase.type = this.opportunity.type.id;
            this.phase.status = -1;
            this.phase.parent = this.opportunity;

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
                modal.loading(false);
                modal.close();
            } catch(e) {
                modal.loading(false);
            }
        },
    }
});