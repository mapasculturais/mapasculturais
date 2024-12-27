app.component('registration-results', {
    template: $TEMPLATES['registration-results'],

    props: {
        registration: {
            type: Entity,
            required: true
        },
        phase: {
            type: Entity,
            required: true
        },
    },
    
    setup(props, { slots }) {
        const hasSlot = name => !!slots[name];
        // os textos estão localizados no arquivo texts.php deste componente 
        const text = Utils.getTexts('registration-results')
        return { text, hasSlot }
    },

    data() {
        return {
            processing: false,
            entity: null,
        }
    },

    methods: {
        async createAppealPhaseRegistration() {
            this.processing = true;
            const messages = useMessages();
        
            const target = this.phase.__objectType === 'evaluationmethodconfiguration' 
                ? this.phase.opportunity 
                : this.phase;

            let args = {
                registration_id: this.registration._id,
            };

            console.log('this.registration', this.registration);

            try {
                await target.POST('createAppealPhaseRegistration', {data: args, callback: (data) => {
                        console.log(data);
                        this.entity = new Entity('registration');
                        this.entity.populate(data);
                        this.processing = false;
                        messages.success(this.text('Solicitação de recurso criada com sucesso'));

                        window.location.href = Utils.createUrl('registration', 'view', [this.entity.id]);
                }});
                    
            } catch (error) {
                console.log(error);
                messages.error(error);
            }
            this.processing = false;
        },
    }
});
