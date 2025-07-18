app.component('opportunity-phase-config-status' , {
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

        if (!this.phase.statusLabels) {
            this.phase.statusLabels = {};
        }

        this.phase.statusLabels[0] = defaultStatuses[0] || 'Não avaliada';

        const statuses = Object.entries(defaultStatuses)
            .filter(([key]) => key !== '0')
            .map(([key, label]) => {
                key = parseInt(key);
                const isActive = key in this.phase.statusLabels;

                return {
                    key,
                    defaultLabel: label,
                    label: isActive ? this.phase.statusLabels[key] : label,
                    enabled: isActive,
                };
            });

        return {
            statuses
        }
    },

    methods: {
        updateStatus(status) {
            if (status.enabled) {
                this.phase.statusLabels[status.key] = status.label;
            } else {
                delete this.phase.statusLabels[status.key];
                status.label = status.defaultLabel;
            }
            this.phase.save(true);
        },

        updateLabel(status) {
            if (status.enabled) {
                this.phase.statusLabels[status.key] = status.label;
                this.phase.save(true);
            }
        }
    }
});