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
                const result = await api.find({
                    'opportunity': `EQ(${this.executionPhase.id})`,
                    '@select': 'id,number,status,editUrl,singleUrl',
                    'status': 'GTE(0)',
                    '@permissions': 'view',
                    'user': 'EQ(@me)',
                });
                this.requests = result;
            } catch (e) {
                // sem pedidos ainda — lista fica vazia
            }
        },

        async createRequest() {
            this.processing = true;
            const messages = useMessages();

            try {
                const data = await this.opportunity.invoke('createExecutionRequest', {
                    registration_id: this.registration.id,
                });

                const entity = new Entity('registration');
                entity.populate(data);
                this.requests.push(entity);

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
                    return 'success__color';
                case 1:
                case 8:
                    return 'warning__color';
                case 3:
                case 2:
                case 0:
                    return 'danger__color';
                default:
                    return '';
            }
        },

        statusLabel(status) {
            const defaults = {
                0:  this.text('Em preenchimento'),
                1:  this.text('Pendente'),
                2:  this.text('Inválida'),
                3:  this.text('Não selecionada'),
                8:  this.text('Suplente'),
                10: this.text('Selecionada'),
            };
            const custom = this.executionPhase?.statusLabels || {};
            return custom[status] ?? defaults[parseInt(status)] ?? this.text('Status desconhecido');
        },
    },
});
