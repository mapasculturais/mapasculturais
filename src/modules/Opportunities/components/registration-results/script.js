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

        hideAppealStatus: {
            type: Boolean,
            default: false,
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

    computed: {
        appealPhase() {
            return this.phase.opportunity;
        },

        appealRegistration() {
            const appealPhaseId = this.appealPhase?.id;
            if (!appealPhaseId) {
                return null;
            }
            return $MAPAS.registrationPhases[appealPhaseId] || this.entity;
        },

        currentEvaluation() {
            return $MAPAS.config.appealPhaseEvaluationForm?.currentEvaluation;
        },

        modalTitle() {
            return this.registration.opportunity.status === -20 ? 
                `${this.text('Detalhamento do recurso para ')} ${this.phase.name} - ${this.registration.number}` :
                `${this.phase.name} - ${this.registration.number}`;
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

        verifyState(registration) {
            switch (registration.status) {
                case 10:
                    return 'success__color';
                    
                case 2 : 
                case 0 : 
				case 3 : 
                    return 'danger__color';
				case 8 : 
                case 1 :
                    return 'warning__color';

                case null:
                default:
                    return '';
            }
        },
    }
});
