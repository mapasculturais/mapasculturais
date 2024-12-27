app.component('registration-status', {
    template: $TEMPLATES['registration-status'],

    props: {
        registration: {
            type: Entity,
            required: true
        },

        phase: {
            type: Entity,
            required: true
        }
    },
    
    setup(props, { slots }) {
        const hasSlot = name => !!slots[name];
        // os textos estão localizados no arquivo texts.php deste componente 
        const text = Utils.getTexts('registration-status');
        return { text, hasSlot }
    },

    data() {
        return {
            processing: false,
            entity: null,
        }
    },

    methods: {
		formatNote(note) {
			note = parseFloat(note);
			return note.toLocaleString($MAPAS.config.locale);
		},
		verifyState(registration) {
            switch (registration.status) {
                case 10:
                    return 'success__color';
                    
                case 2 : 
                case 0 : 

                    return 'danger__color';
				case 3 : 
				case 8 : 
                    return 'warning__color';

                case null:
                default:
                    return '';
            }
        },

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
        }
    }
});
