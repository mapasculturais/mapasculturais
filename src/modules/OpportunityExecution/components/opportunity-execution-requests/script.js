app.component('opportunity-execution-requests', {
    template: $TEMPLATES['opportunity-execution-requests'],

    setup() {
        const text = Utils.getTexts('opportunity-execution-requests');
        return { text };
    },

    props: {
        registration: {
            type: Entity,
            required: true,
        },
        phase: {
            type: Entity,
            required: true,
        },
        phases: {
            type: Array,
            required: true,
        },
    },

    data() {
        return {
            processing: false,
            showForm: false,
            selectedCategory: '',
            requests: [],
        };
    },

    mounted() {
        if (this.executionPhase) {
            this.loadRequests();
        }
    },

    computed: {
        firstPhase() {
            return this.phases[0];
        },

        opportunity() {
            return this.phase.__objectType === 'evaluationmethodconfiguration'
                ? this.phase.opportunity
                : this.phase;
        },

        executionPhase() {
            return this.phases.find(p => p.isExecutionPhase) || null;
        },

        canOpenRequest() {
            // só agentes com inscrição aprovada podem abrir pedidos
            return this.registration?.status == 10;
        },
    },

    methods: {
        async loadRequests() {
            if (!this.executionPhase?.id) return;

            try {
                const api = new API('registration');
                const result = await api.find(`opportunity:${this.executionPhase.id},@opportunity:${this.opportunity.id}`);
                this.requests = result.map(r => {
                    const e = new Entity('registration');
                    e.populate(r);
                    return e;
                });
            } catch (e) {
                // sem pedidos ainda — lista fica vazia
            }
        },

        async createRequest() {
            if (!this.selectedCategory) return;

            this.processing = true;
            const messages = useMessages();

            try {
                const data = await this.opportunity.invoke('createExecutionRequest', {
                    category: this.selectedCategory,
                    registration_id: this.registration.id,
                });

                const entity = new Entity('registration');
                entity.populate(data);
                this.requests.push(entity);

                this.showForm = false;
                this.selectedCategory = '';
                messages.success(this.text('Pedido criado com sucesso'));

                window.location.href = Utils.createUrl('registration', 'view', [entity.id]);
            } catch (error) {
                messages.error(error.data ?? error);
            }

            this.processing = false;
        },

        fillForm(req) {
            window.location.href = req.editUrl;
        },

        statusColor(status) {
            switch (parseInt(status)) {
                case 10:
                case 1:
                    return 'success__color';
                case 3:
                case 2:
                case 0:
                    return 'danger__color';
                case 8:
                    return 'warning__color';
                default:
                    return '';
            }
        },

        statusLabel(status) {
            const labels = this.executionPhase?.statusLabels || {};
            return labels[status] ?? this.text('Status desconhecido');
        },
    },
});
