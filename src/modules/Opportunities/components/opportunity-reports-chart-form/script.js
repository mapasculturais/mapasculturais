app.component('opportunity-reports-chart-form', {
    template: $TEMPLATES['opportunity-reports-chart-form'],

    props: {
        opportunityId: {
            type: [String, Number],
            required: true,
        },
        filters: {
            type: Object,
            required: true, // { status, proponentTypes }
        },
        availableFields: {
            type: Array,
            required: true,
        },
        graphic: {
            type: Object,
            default: null, // { reportData: {graphicId, title, description, typeGraphic, columns, groupData}, data }
        },
    },

    emits: ['saved', 'cancelled'],

    setup() {
        // os textos estão localizados no arquivo texts.php deste componente
        const text = Utils.getTexts('opportunity-reports-chart-form');
        return { text };
    },

    data() {
        const reportData = (this.graphic && this.graphic.reportData) || {};
        const columns = reportData.columns || [];
        return {
            title: reportData.title || '',
            description: reportData.description || '',
            typeGraphic: reportData.typeGraphic || 'pie',
            fieldAIndex: this.findFieldIndex(columns[0]),
            fieldBIndex: this.findFieldIndex(columns[1]),
            groupData: reportData.groupData || false,
            preview: this.graphic ? this.graphic.data : null,
            loadingPreview: false,
            saving: false,
            error: null,
            previewTimeout: null,
        };
    },

    watch: {
        typeGraphic() {
            this.schedulePreview();
        },
        fieldAIndex() {
            this.schedulePreview();
        },
        fieldBIndex() {
            this.schedulePreview();
        },
        filters: {
            deep: true,
            handler() {
                this.schedulePreview();
            },
        },
    },

    created() {
        this.fetchPreview();
    },

    methods: {
        findFieldIndex(column) {
            if (!column) {
                return '';
            }
            const index = this.availableFields.findIndex(field => (
                field.value === column.value && JSON.stringify(field.source) === JSON.stringify(column.source)
            ));
            return index >= 0 ? index : '';
        },

        buildColumns() {
            const columns = [];
            if (this.fieldAIndex !== '' && this.fieldAIndex !== null) {
                columns.push(this.availableFields[this.fieldAIndex]);
            }
            if (this.typeGraphic !== 'pie' && this.fieldBIndex !== '' && this.fieldBIndex !== null) {
                columns.push(this.availableFields[this.fieldBIndex]);
            }
            return columns;
        },

        schedulePreview() {
            clearTimeout(this.previewTimeout);
            this.previewTimeout = setTimeout(() => this.fetchPreview(), 400);
        },

        async fetchPreview() {
            const columns = this.buildColumns();
            if (!columns.length) {
                this.preview = null;
                return;
            }
            this.loadingPreview = true;
            const api = new ReportsAPI();
            try {
                this.preview = await api.previewGraphic(this.opportunityId, { columns, typeGraphic: this.typeGraphic }, this.filters);
            } finally {
                this.loadingPreview = false;
            }
        },

        async submit() {
            const columns = this.buildColumns();
            if (!columns.length) {
                this.error = this.text('error_no_field');
                return;
            }

            this.saving = true;
            this.error = null;

            const payload = {
                opportunity_id: this.opportunityId,
                status: this.filters.status,
                proponentType: this.filters.proponentTypes,
                range: this.filters.ranges,
                typeGraphic: this.typeGraphic,
                columns,
                title: this.title,
                description: this.description,
                fields: columns.map(c => c.label).join(' x '),
                groupData: this.groupData,
            };

            if (this.graphic && this.graphic.reportData && this.graphic.reportData.graphicId) {
                payload.graphicId = this.graphic.reportData.graphicId;
            }

            const api = new ReportsAPI();
            const result = await api.saveGraphic(payload);
            this.saving = false;

            if (result.error) {
                this.error = this.text('error_no_data');
                return;
            }

            this.$emit('saved');
        },
    },
});
