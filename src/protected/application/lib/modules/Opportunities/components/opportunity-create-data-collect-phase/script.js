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
            return this.dateEnd;
        },
        minDate () {
            return this.dateStart;
        },
        minDateRegistrationTo () {
            return this.phase.registrationFrom?._date || '';
        }
    },

    mounted () {
        this.dateStart = $MAPAS.requestedEntity.registrationFrom.date;
        this.dateEnd = $MAPAS.requestedEntity.registrationTo.date;
    },

    methods: {
        dateFormat(date) {
            return new Date(date).toLocaleString();
        },
        createEntity() {
            this.phase = Vue.ref(new Entity('opportunity'));
            console.log(this.opportunity);
            this.phase.ownerEntity = this.opportunity.ownerEntity;
            console.log(this.phase);
            this.phase.type = this.opportunity.type;
            this.phase.status = -1;
            this.phase.parent = this.opportunity;

        },
        destroyEntity() {
            // para o conteúdo da modal não sumir antes dela fechar
            setTimeout(() => this.entity = null, 200);
        },
        save(modal) {
            modal.loading(true);
            this.phase.save().then((response) => {
                this.$emit('create', response);
                modal.loading(false);
                modal.close();
            }).catch((e) => {
                modal.loading(false);

            });
        },
    }
});