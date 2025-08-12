app.component('opportunity-phase-config-status', {
    template: $TEMPLATES['opportunity-phase-config-status'],

    setup() {
        const text = Utils.getTexts('opportunity-phase-config-status');
        return { text };
    },

    props: {
        phase: {
            type: Entity,
            required: true
        },
    },

    data() {
        const defaultStatuses = $MAPAS.config.opportunityPhaseConfigStatus.statuses || {};

        if (Array.isArray(this.phase.statusLabels)) {
            const converted = {};
            this.phase.statusLabels.forEach((label, index) => {
                converted[String(index)] = label;
            });
            this.phase.statusLabels = converted;
        } else if (!this.phase.statusLabels || typeof this.phase.statusLabels !== 'object') {
            this.phase.statusLabels = {};
        }

        this.phase.statusLabels['0'] = defaultStatuses['0'] || this.text('Rascunho');

        const statuses = Object.entries(defaultStatuses)
            .filter(([key]) => key !== '0')
            .map(([key, label]) => {
                const isActive = key in this.phase.statusLabels;

                return {
                    key,
                    defaultLabel: label,
                    label: isActive ? this.phase.statusLabels[key] : label,
                    enabled: isActive,
                    showOriginal: false,
                    isEditing: false,
                };
            });

        return {
            statuses
        }
    },

    methods: {
        updateStatus(status) {
            const key = String(status.key);
            if (status.enabled) {
                if (!status.label || status.label.trim() === '') {
                    status.label = status.defaultLabel;
                }
                this.phase.statusLabels[key] = status.label;
            } else {
                status.label = status.defaultLabel;
                status.isEditing = false;
                delete this.phase.statusLabels[key];
            }
            this.phase.save();
        },

        updateLabel(status) {
            if (status.enabled) {
                const key = String(status.key);
                this.phase.statusLabels[key] = status.label;
                this.phase.save(true);
            }
        },

        restoreOriginal(status) {
            const key = String(status.key);
            this.phase.statusLabels[key] = status.defaultLabel;
            status.label = status.defaultLabel;
            this.phase.save(true);
        },

        toggleEdit(status) {
            status.isEditing = !status.isEditing;
        }
    }
});
