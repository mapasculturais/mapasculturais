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

        tab: {
            type: String,
        },
    },

    data() {
        return {
            processing: false,
            phaseData: {},
            entity: null,
            moreResponse: false,   
        }
    },

    mounted() {
        this.initializeAppealPhase();
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

        /**
         * Quantos dos 5 fluxos de notificação da fase de recurso estão ativos.
         * Alimenta o badge "X de 5 ativas" no título do accordion de Notificações.
         * Computado direto da entidade (fase de recurso filha), não do sub-componente
         * notify-config — assim o badge é reativo mesmo com o accordion fechado.
         *
         * Lógica espelha Module::isFlowEnabled: null/false/'0' -> off; true/'1' -> on.
         */
        notifyActiveFlowCount() {
            const flows = ['appealCreated', 'appealSent', 'statusNotApproved', 'statusApproved', 'statusInvalid'];
            return flows.reduce((count, flow) => {
                const v = this.entity?.['appealNotify_' + flow + '_enabled'];
                return count + (v === true || v === '1' || v === 1 ? 1 : 0);
            }, 0);
        },

        /**
         * Classe do badge de notificações conforme spec UX (Seção 4):
         *   0     -> warning (cor de atenção, onboarding)
         *   1..4  -> neutra
         *   5     -> success
         */
        notifyBadgeClass() {
            const c = this.notifyActiveFlowCount;
            if (c === 0) return 'opportunity-appeal-phase-config__notify-badge--warning';
            if (c === 5) return 'opportunity-appeal-phase-config__notify-badge--success';
            return 'opportunity-appeal-phase-config__notify-badge--neutral';
        },

        fromDateMin() {
            return this.phase.publishTimestamp || this.phase.registrationFrom || this.phase.evaluationMethodConfiguration?.evaluationFrom;
        },

        fromDateMax() {
            return null;
        },

        toDateMin() {
            return this.phase.appealPhase?.registrationFrom || this.phase.appealPhase?.evaluationMethodConfiguration?.evaluationFrom;
        },

        toDateMax() {
            return null;
        },
    },

    methods: {
        async createAppealPhase() {
            this.processing = true;
            const messages = useMessages();
        
            const target = this.phase.__objectType === 'evaluationmethodconfiguration' 
                ? this.phase.opportunity 
                : this.phase;
        
            let args = {};
        
            await target.POST('createAppealPhase', args)
                .then((data) => {
                    this.phaseData = data;
        
                    this.entity = new Entity('opportunity');
                    this.entity.populate(this.phaseData);
                    this.processing = false;
        
                    messages.success(this.text('Fase de recurso criada com sucesso'));
                })
                .catch((error) => {
                    messages.error(error.data);
                    this.processing = false;
                });
        },

        initializeAppealPhase() {
            this.entity = this.phase.appealPhase;
        },

        async deleteAppealPhase() {
            const messages = useMessages();
            this.processing = true;
            const entity = this.entity;

            try {
                this.entity = null;
                await entity.destroy();
                messages.success(this.text('Fase de recurso excluída com sucesso'));
                
            } catch (error) {
                this.entity = entity;
                messages.error(error);
            }

            this.processing = false;         
        }

    }
});