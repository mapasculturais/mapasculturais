app.component('opportunity-basic-info' , {
    template: $TEMPLATES['opportunity-basic-info'],

    setup() {
        const text = Utils.getTexts('opportunity-basic-info');
        return { text }
    },

    data () {
        return {
            continuousFlowDate: $MAPAS.config.opportunityBasicInfo.date,
            phases: []
        };
    },

    async created() {
        if($MAPAS.opportunityPhases && $MAPAS.opportunityPhases.length > 0) {
            this.phases = $MAPAS.opportunityPhases;
        } else {
            const api = new OpportunitiesAPI();
            this.phases = await api.getPhases(this.entity.id);
        }
    },

    props: {
        entity: {
            type: Entity,
            required: true
        }
    },

    computed: {
        lastPhase () {
            const phase = this.phases.find(item => item.isLastPhase);
            return phase;
        }
    },

    watch: {
        'entity.isContinuousFlow'(newVal, oldValue) {
            if(Boolean(newVal) != Boolean(oldValue)){
                if (!newVal) {
                    this.entity.hasEndDate = false;
                    this.entity.continuousFlow = null;
                    this.entity.publishedRegistrations = false;

                    if (this.entity.registrationFrom && this.entity.registrationFrom._date instanceof Date) {
                        this.incrementRegistrationTo();
                    } 
                       
                    this.lastPhase.name = this.text("Publicação final do resultado");
                       
                } else {
                    const myDate = new McDate(new Date(this.continuousFlowDate));
                    
                    this.entity.continuousFlow = myDate.sql('full');
                    this.entity.registrationTo = myDate.sql('full');
                    this.entity.publishedRegistrations = true;

                    if(!this.entity.registrationFrom){
                        let actualDate = new Date();
                        this.entity.registrationFrom = Vue.reactive(new McDate(actualDate));
                    }
                    
                    this.lastPhase.name = this.text("Resultado");
                }

                this.lastPhase.disableMessages();
                this.lastPhase.save();
                this.entity.save();
            }
        },

        'entity.hasEndDate'(newVal, oldValue) {
            if(Boolean(newVal) != Boolean(oldValue)){
                if (this.entity.isContinuousFlow) {
                    if(newVal){
                        this.entity.continuousFlow = null;
                        this.entity.registrationTo = null;
                        this.entity.publishedRegistrations = false;

                        if (this.entity.registrationFrom && this.entity.registrationFrom._date instanceof Date) {
                           this.incrementRegistrationTo();
                        } 

                    } else {
                        const myDate = new McDate(new Date(this.continuousFlowDate));
                        
                        this.entity.continuousFlow = myDate;
                        this.entity.registrationTo = myDate;
                    }
                } 
            }
        },
    },

    methods: {
        incrementRegistrationTo (){
            let newDate = new Date(this.entity.registrationFrom._date);
            newDate.setDate(newDate.getDate() + 2);
    
            this.entity.registrationTo = new McDate(newDate);
        },

        createEntities() {
            this.collectionPhase = reactive(new Entity('opportunity'));
            this.evaluationPhase = reactive(new Entity('evaluationmethodconfiguration'));
        },
    }
});