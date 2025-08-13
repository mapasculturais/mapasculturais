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
        return {
            statuses: []
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
        },

        defaultStatuses() {
            const defaultMap = {
                appeal: {
                    0: this.text('Rascunho'),
                    1: this.text('Aguardando resposta'),
                    2: this.text('Negado'),
                    3: this.text('Indeferido'),
                    10: this.text('Deferido')
                },
                reporting: {
                    0: this.text('Rascunho'),
                    1: this.text('Pendente'),
                    2: this.text('Inválida'),
                    3: this.text('Reprovado'),
                    8: this.text('Aprovado com ressalva'),
                    10: this.text('Aprovado')
                },
                default: {
                    0: this.text('Rascunho'),
                    1: this.text('Pendente'),
                    2: this.text('Inválida'),
                    3: this.text('Não selecionada'),
                    8: this.text('Suplente'),
                    10: this.text('Selecionada')
                }
            };

            const type = this.phase?.isAppealPhase ? 'appeal' : this.phase?.isReportingPhase ? 'reporting' : 'default';

            const selected = defaultMap[type] || {};
            const labels = this.phase.statusLabels || {};

            return Object.entries(selected).map(([key, defaultLabel]) => {
                const statusKey = String(key);
                const isEnabled = Object.prototype.hasOwnProperty.call(labels, statusKey);

                return {
                    key: Number(key),
                    defaultLabel,
                    label: isEnabled ? labels[statusKey] : defaultLabel,
                    enabled: isEnabled,
                    isEditing: false
                };
            });
        }
    },

    mounted() {
        this.statuses = this.defaultStatuses();
    },
});
