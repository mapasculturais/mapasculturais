app.component('create-opportunity', {
    template: $TEMPLATES['create-opportunity'],
    emits: ['create'],

    setup() {
        // os textos estão localizados no arquivo texts.php deste componente 
        const text = Utils.getTexts('create-opportunity')
        return { text }
    },

    data() {
        return {
            continuousFlowDate: $MAPAS.config.createOpportunity.date,
            entity: null,
            fields: [],
        }
    },

    props: {
        editable: {
            type: Boolean,
            default: true
        },
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
                       
                } else {
                    // Desmarca publicityOnly se marcar continuousFlow
                    if (this.entity.publicityOnly) {
                        this.entity.publicityOnly = false;
                    }
                    
                    const myDate = new McDate(new Date(this.continuousFlowDate));
                    
                    this.entity.continuousFlow = myDate.sql('full');
                    this.entity.registrationTo = myDate.sql('full');
                    this.entity.publishedRegistrations = true;

                    if(!this.entity.registrationFrom){
                        let actualDate = new Date();
                        this.entity.registrationFrom = Vue.reactive(new McDate(actualDate));
                    }
                }
            }
        },

        'entity.publicityOnly'(newVal, oldValue) {
            if(Boolean(newVal) != Boolean(oldValue)){
                if (newVal) {
                    // Desmarca fluxo contínuo se marcar publicityOnly
                    if (this.entity.isContinuousFlow) {
                        this.entity.isContinuousFlow = false;
                        this.entity.hasEndDate = false;
                        this.entity.continuousFlow = null;
                    }
                    
                    // Garante que as datas sejam obrigatórias
                    if(!this.entity.registrationFrom){
                        let actualDate = new Date();
                        this.entity.registrationFrom = Vue.reactive(new McDate(actualDate));
                    }
                    
                    if (this.entity.registrationFrom && !this.entity.registrationTo) {
                        this.incrementRegistrationTo();
                    }
                }
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

    computed: {
        areaClasses() {
            return this.areaErrors ? 'field error' : 'field';
        },
        
        modalTitle() {
            if (!this.entity?.id) {
                return __('criarOportunidade', 'create-opportunity');
                console.log(this.entity.id);
            }
            if(this.entity.status==0){
                return __('oportunidadeCriada', 'create-opportunity');

            }
        },
    },

    methods: {
        handleSubmit(event) {
            event.preventDefault();
        },    

        createEntity() {
            this.entity = new Entity('opportunity');
            this.entity.terms = { area: [] }
        },

        createDraft(modal) {
            this.entity.status = 0;
            this.save(modal);
        },

        createPublic(modal) {
            //lançar dois eventos
            this.entity.status = 1;
            this.save(modal);
        },

        save(modal) {
            modal.loading(true);
            this.entity.save().then((response) => {
                this.$emit('create', response);
                modal.loading(false);
                Utils.pushEntityToList(this.entity);
            }).catch((e) => {
                modal.loading(false);
            });
        },

        destroyEntity() {
            // para o conteúdo da modal não sumir antes dela fechar
            setTimeout(() => {
                this.entity = null;
            }, 200);
        },

        incrementRegistrationTo (){
            let newDate = new Date(this.entity.registrationFrom._date);
            newDate.setDate(newDate.getDate() + 2);
    
            this.entity.registrationTo = new McDate(newDate);
        },
    },
});
