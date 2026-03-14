app.component('opportunity-execution-phase-config', {
    template: $TEMPLATES['opportunity-execution-phase-config'],

    setup() {
        const text = Utils.getTexts('opportunity-execution-phase-config');
        return { text };
    },

    props: {
        phase: {
            type: Entity,
            required: true,
        },
        phases: {
            type: Array,
            required: true,
        },
        tab: {
            type: String,
        },
    },

    data() {
        return {
            processing: false,
            entity: null,
        };
    },

    mounted() {
        this.entity = this.phases.find(p => p.isExecutionPhase) || null;
    },

    computed: {
        firstPhase() {
            return this.phases[0];
        },
    },

    methods: {
        async createExecutionPhase() {
            this.processing = true;
            const messages = useMessages();

            try {
                const data = await this.phase.POST('createExecutionPhase', {});
                this.entity = new Entity('opportunity');
                this.entity.populate(data);
                messages.success(this.text('Fase de execução criada com sucesso'));
            } catch (error) {
                messages.error(error.data ?? error);
            }

            this.processing = false;
        },

        async deleteExecutionPhase() {
            const messages = useMessages();
            this.processing = true;
            const entity = this.entity;

            try {
                this.entity = null;
                await entity.destroy();
                messages.success(this.text('Fase de execução excluída com sucesso'));
            } catch (error) {
                this.entity = entity;
                messages.error(error);
            }

            this.processing = false;
        },
    },
});
