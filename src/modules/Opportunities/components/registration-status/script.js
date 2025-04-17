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
            appealPhaseRegistrationFrom: this.registration.opportunity.appealPhase?.registrationFrom,
            appealPhaseRegistrationTo: this.registration.opportunity.appealPhase?.registrationTo,
            appealPhaseEvaluationFrom: this.registration.opportunity.appealPhase?.evaluationMethodConfiguration.evaluationFrom,
            appealPhaseEvaluationTo: this.registration.opportunity.appealPhase?.evaluationMethodConfiguration.evaluationTo,
        }
    },

    computed: {
        appealPhase() {
            return this.opportunity.isAppealPhase ? this.opportunity : this.opportunity.appealPhase;
        },

        appealRegistration() {
            const appealPhaseId = this.appealPhase?.id;
            if (!appealPhaseId) {
                return null;
            }

            return $MAPAS.registrationPhases[appealPhaseId] || this.entity;
        },

        hideAppealStatus() {
            if (this.registration.opportunity.isReportingPhase) {
                return true;
            }
            return this.registration.status == 1 || this.registration.status == 10;
        },

        opportunity () {
            if (this.phase.__objectType === 'evaluationmethodconfiguration') {
                return this.phase.opportunity;
            } else {
                return this.phase;
            }
        },

        showRegistrationResults() {
            const { isReportingPhase, __objectType, publishEvaluationDetails } = this.phase;
            const { allow_proponent_response } = this.registration.opportunity;

            if (isReportingPhase === '1' && __objectType === 'opportunity' && allow_proponent_response == '1') {
                return false;
            }

            return publishEvaluationDetails || allow_proponent_response === '1';
        },
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
				case 3 : 
                    return 'danger__color';
				case 8 : 
                case 1 :
                case undefined:
                    return 'warning__color';

                case null:
                default:
                    return '';
            }
        },

        async createAppealPhaseRegistration() {
            this.processing = true;
            const messages = useMessages();
        
            const target = this.opportunity;

            const args = {
                registration_id: this.registration._id,
            };

            try {
                await target.POST('createAppealPhaseRegistration', {data: args, callback: (data) => {
                        this.entity = new Entity('registration');
                        this.entity.populate(data);
                        this.processing = false;
                        messages.success(this.text('Solicitação de recurso criada com sucesso'));

                        window.location.href = Utils.createUrl('registration', 'view', [this.entity.id]);
                }});
                    
            } catch (error) {
                console.error(error);
                messages.error(error.data ?? error);
            }
            this.processing = false;
        },

        fillFormButton() {
            window.location.href = this.appealRegistration.editUrl;
        },

        dateFrom() {
			if (this.appealPhaseRegistrationFrom) {
				return this.appealPhaseRegistrationFrom.date('2-digit year');
			}	
			if (this.appealPhaseEvaluationFrom) {
				return this.appealPhaseEvaluationFrom.date('2-digit year');
			}
			return false;
		},

		dateTo() {
			if (this.appealPhaseRegistrationTo) {
				return this.appealPhaseRegistrationTo.date('2-digit year');
			}	
			if (this.appealPhaseEvaluationTo) {
				return this.appealPhaseEvaluationTo.date('2-digit year');
			}
			return false;
		},

		hour() {
			if (this.appealPhaseRegistrationTo) {
				return this.appealPhaseRegistrationTo.time();
			}
			if (this.appealPhaseEvaluationTo) {
				return this.appealPhaseEvaluationTo.time();
			}
			return false;
		},

        redirectToRegistrationForm() {
            return window.location.hash = "#ficha";
        },
        
        shouldShowResults(item) {
			// se é uma fase de avaliação que não tem uma fase de coleta de dados anterior
			const isEvaluation = item.__objectType == 'evaluationmethodconfiguration';

			// se é uma fase de coleta de dados que não tem uma fase de avaliação posterior
			const isRegistrationOnly = item.__objectType == 'opportunity' && !item.evaluationMethodConfiguration;

			const phaseOpportunity = item.__objectType == 'opportunity' ? item : item.opportunity;

			return phaseOpportunity.publishedRegistrations && (isRegistrationOnly || isEvaluation);
		
		},
    }
});
