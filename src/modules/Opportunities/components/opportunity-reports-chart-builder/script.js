app.component('opportunity-reports-chart-builder', {
    template: $TEMPLATES['opportunity-reports-chart-builder'],

    props: {
        opportunityId: {
            type: [String, Number],
            required: true,
        },
        filters: {
            type: Object,
            required: true, // { status, proponentTypes }
        },
    },

    data() {
        return {
            graphics: [],
            availableFields: [],
            loading: false,
            editingGraphic: null,
        };
    },

    watch: {
        filters: {
            deep: true,
            handler() {
                this.fetchGraphics();
            },
        },
    },

    async created() {
        this.loading = true;
        const api = new ReportsAPI();
        const [graphics, fields] = await Promise.all([
            api.getGraphics(this.opportunityId, this.filters),
            api.getReportFields(this.opportunityId),
        ]);
        this.graphics = graphics;
        this.availableFields = fields;
        this.loading = false;
    },

    methods: {
        async fetchGraphics() {
            const api = new ReportsAPI();
            this.graphics = await api.getGraphics(this.opportunityId, this.filters);
        },

        openCreateForm() {
            this.editingGraphic = null;
            this.$refs.formModal.open();
        },

        openEditForm(graphic) {
            this.editingGraphic = graphic;
            this.$refs.formModal.open();
        },

        onSaved(close) {
            close();
            this.fetchGraphics();
        },

        async removeGraphic(graphicId) {
            const api = new ReportsAPI();
            await api.deleteGraphic(this.opportunityId, graphicId);
            this.fetchGraphics();
        },
    },
});
