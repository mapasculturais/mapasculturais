app.component('opportunity-phases-timeline', {
	template: $TEMPLATES['opportunity-phases-timeline'],

	props: {
		big: {
			type: Boolean,
			default: false
		},
		center: {
			type: Boolean,
			default: false
		}
	},

    async created() {
        if($MAPAS.opportunityPhases && $MAPAS.opportunityPhases.length > 0) {
            this.phases = $MAPAS.opportunityPhases;
        } else {
            const api = new OpportunitiesAPI();
            this.phases = await api.getPhases(this.entity.id);
        }
    },

	data() {
        return {
			phases: [],
        }
    },

	methods: {		
		dateFrom(item) {
			if (item.registrationFrom) {
				return item.registrationFrom.date('2-digit year');
			}	
			if (item.evaluationFrom) {
				return item.evaluationFrom.date('2-digit year');
			}
			return false;
		},

		dateTo(item) {
			if (item.registrationTo) {
				return item.registrationTo.date('2-digit year');
			}	
			if (item.evaluationTo) {
				return item.evaluationTo.date('2-digit year');
			}
			return false;
		},

		hour(item) {
			if (item.registrationTo) {
				return item.registrationTo.time();
			}
			if (item.evaluationTo) {
				return item.evaluationTo.time();
			}
			return false;
		},

		isActive(item) {
			if (item.isLastPhase) {
				return !item.publishedRegistrations && item.publishTimestamp?.isPast();
			}

			if (item.registrationFrom && item.registrationTo) {
				return item.registrationFrom.isPast() && item.registrationTo.isFuture();
			}

			if (item.evaluationFrom && item.evaluationTo) {
				return item.evaluationFrom.isPast() && item.evaluationTo.isFuture();
			}

			return false;
		},

		isDataCollectionPhase(item) {
			return item.__objectType == 'opportunity' && !item.isLastPhase;
		},

		isEvaluationPhase(item) {
			return item.__objectType == 'evaluationmethodconfiguration';
		},

		itHappened(item) {
			if (item.isLastPhase) {
				return item.publishedRegistrations;
			}

			if (item.__objectType == 'opportunity') {
				return item.registrationTo?.isPast();
			}
			
			if (item.__objectType == 'evaluationmethodconfiguration') {
				return item.evaluationTo?.isPast();
			}
			
			return false;
		},

		shouldShowResults(item) {
			// se é uma fase de avaliação que não tem uma fase de coleta de dados anterior
			const isEvaluation = item.__objectType == 'evaluationmethodconfiguration';

			// se é uma fase de coleta de dados que não tem uma fase de avaliação posterior
			const isRegistrationOnly = item.__objectType == 'opportunity' && !item.evaluationMethodConfiguration;

			const phaseOpportunity = item.__objectType == 'opportunity' ? item : item.opportunity;

			return phaseOpportunity.publishedRegistrations && (isRegistrationOnly || isEvaluation);
		
		},

		getRegistration(item) {
			const phaseOpportunity = item.__objectType == 'opportunity' ? item : item.opportunity;
			
			return $MAPAS.registrationPhases ? $MAPAS.registrationPhases[phaseOpportunity.id] : null;
		},
	}
});