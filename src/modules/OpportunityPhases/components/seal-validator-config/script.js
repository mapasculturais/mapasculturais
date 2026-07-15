/**
 * seal-validator-config
 *
 * Componente Vue de configuração dos selos validadores por fase de avaliação.
 *
 * Persiste o metadado `sealExemptionConfig = { seals: [ids] }`
 * no EvaluationMethodConfiguration recebido via prop `entity`.
 *
 * - Selos sem permissão: ocultos (lista vem filtrada do init.php); contador
 *   de transparência no rodapé (deniedSealsCount).
 * - Bloqueio read-only quando `entity.canEditSealConfig === false` (flag
 *   calculada server-side — nunca no cliente, para evitar divergência de fuso).
 * - "Habilitado" deriva de `seals.length > 0` (spec §3.1: sem campo `enabled`
 *   redundante). Desligar o toggle remove todos os selos e persiste `{ seals: [] }`.
 * - Pendências: invalidadores do selo ausentes no formulário (init.php);
 *   selos com pendência ficam marcados em amarelo.
 *
 * Spec §4.1 / §4.2.
 */
app.component('seal-validator-config', {
    template: $TEMPLATES['seal-validator-config'],

    emits: ['changed'],

    props: {
        // EvaluationMethodConfiguration da fase.
        entity: {
            type: Entity,
            required: true,
        },
    },

    setup() {
        const text = Utils.getTexts('seal-validator-config');
        const messages = useMessages();
        return { text, messages };
    },

    data() {
        return {
            expanded: false,
            saving: false,
        };
    },

    beforeMount() {
        this.ensureConfigStructure();
        this.expanded = this.isEnabled;
    },

    computed: {
        config() {
            return this.entity.sealExemptionConfig;
        },

        // Estado real: a isenção está ativa quando há ao menos 1 selo.
        isEnabled() {
            return Array.isArray(this.config?.seals) && this.config.seals.length > 0;
        },

        availableSeals() {
            return $MAPAS?.config?.sealValidatorConfig?.availableSeals || [];
        },

        deniedSealsCount() {
            return $MAPAS?.config?.sealValidatorConfig?.deniedSealsCount || 0;
        },

        hasAvailableSeals() {
            return this.availableSeals.length > 0;
        },

        canEdit() {
            if (this.entity.canEditSealConfig === undefined) {
                return false;
            }
            return this.entity.canEditSealConfig !== false;
        },

        sealById() {
            const map = {};
            this.availableSeals.forEach((s) => {
                map[s.value] = s;
                map[String(s.value)] = s;
            });
            return map;
        },

        sealLabels() {
            const map = {};
            this.availableSeals.forEach((s) => {
                map[s.value] = s.label;
                map[String(s.value)] = s.label;
            });
            return map;
        },

        inactiveSelectedSeals() {
            const known = {};
            this.availableSeals.forEach((s) => {
                known[s.value] = true;
                known[String(s.value)] = true;
            });
            return (this.config?.seals || []).filter((id) => !known[id] && !known[String(id)]);
        },

        selectedCount() {
            return (this.config?.seals || []).length;
        },

        selectedSealsWithStatus() {
            return (this.config?.seals || []).map((id) => {
                const seal = this.sealById[id] || this.sealById[String(id)];
                const missing = seal?.missingInvalidators || [];
                return {
                    id,
                    label: seal?.label || this.sealLabels[id] || this.sealLabels[String(id)] || String(id),
                    missingInvalidators: missing,
                    hasPending: missing.length > 0,
                };
            });
        },

        pendingSeals() {
            return this.selectedSealsWithStatus.filter((s) => s.hasPending);
        },

        hasPendingInvalidators() {
            return this.pendingSeals.length > 0;
        },
    },

    methods: {
        normalizeSealIds(seals) {
            return Array.isArray(seals)
                ? seals.map((id) => parseInt(id, 10)).filter((id) => !Number.isNaN(id))
                : [];
        },

        ensureConfigStructure() {
            if (!this.entity.sealExemptionConfig || typeof this.entity.sealExemptionConfig !== 'object') {
                this.entity.sealExemptionConfig = { seals: [] };
            }
            if (!Array.isArray(this.entity.sealExemptionConfig.seals)) {
                this.entity.sealExemptionConfig.seals = [];
            }
            const normalized = this.normalizeSealIds(this.entity.sealExemptionConfig.seals);
            if (JSON.stringify(normalized) !== JSON.stringify(this.entity.sealExemptionConfig.seals)) {
                this.entity.sealExemptionConfig = { seals: normalized };
            }
        },

        /**
         * Atribui novo objeto ao metadado para que o Entity.data() detecte mudança
         * (mutação in-place não altera __originalValues).
         */
        assignSealConfig(seals) {
            this.entity.sealExemptionConfig = { seals: this.normalizeSealIds(seals) };
        },

        async onToggleEnabled(value) {
            if (!this.canEdit) {
                return;
            }

            if (value) {
                this.expanded = true;
                return;
            }

            if (this.isEnabled) {
                await this.removeAllSeals();
                return;
            }

            this.expanded = false;
        },

        onSealSelected() {
            this.persist();
        },

        onSealRemoved() {
            this.persist();
        },

        removeSelectedSeal(id) {
            if (!this.canEdit) {
                return;
            }
            const seals = [...(this.config?.seals || [])];
            const index = seals.findIndex((s) => s === id || String(s) === String(id));
            if (index >= 0) {
                seals.splice(index, 1);
                this.assignSealConfig(seals);
                this.persist();
            }
        },

        async removeAllSeals() {
            if (!this.canEdit) {
                return;
            }
            try {
                this.assignSealConfig([]);
                await this.persist(0);
                this.expanded = false;
                this.messages.success(this.text('removeSuccess'));
            } catch (e) {
                this.messages.error(e?.data?.message || this.text('removeError'));
            }
        },

        persist(delay = 300) {
            if (!this.canEdit) {
                return;
            }
            clearTimeout(this.__sealValidatorSaveTimeout);
            this.__sealValidatorSaveTimeout = setTimeout(async () => {
                this.saving = true;
                try {
                    const seals = [...(this.config?.seals || [])];
                    this.assignSealConfig(seals);
                    await this.entity.save();
                    this.ensureConfigStructure();
                    this.$emit('changed', this.entity.sealExemptionConfig);
                } catch (e) {
                    this.messages.error(e?.data?.message || this.text('saveError'));
                } finally {
                    this.saving = false;
                }
            }, delay);
        },
    },
});
