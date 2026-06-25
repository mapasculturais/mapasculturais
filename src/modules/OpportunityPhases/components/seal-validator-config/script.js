/**
 * seal-validator-config
 *
 * Componente Vue de configuração dos selos validadores por fase de avaliação.
 *
 * Persiste o metadado `sealExemptionConfig = { seals: [ids], label: string }`
 * no EvaluationMethodConfiguration recebido via prop `entity`.
 *
 * - Selos sem permissão: ocultos (lista vem filtrada do init.php); contador
 *   de transparência no rodapé (deniedSealsCount).
 * - Rótulo: opcional com fallback para o rótulo padrão localizado.
 * - Bloqueio read-only quando `entity.canEditSealConfig === false` (flag
 *   calculada server-side — nunca no cliente, para evitar divergência de fuso).
 * - "Habilitado" deriva de `seals.length > 0` (spec §3.1: sem campo `enabled`
 *   redundante).
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
            // Controla expansão da área de configuração (UI), independente do
            // estado real (que deriva de seals.length > 0).
            expanded: false,
            saving: false,
        };
    },

    beforeMount() {
        // Garante a estrutura do metadado antes do primeiro render.
        if (!this.entity.sealExemptionConfig || typeof this.entity.sealExemptionConfig !== 'object') {
            this.entity.sealExemptionConfig = { seals: [], label: '' };
        }
        if (!Array.isArray(this.entity.sealExemptionConfig.seals)) {
            this.entity.sealExemptionConfig.seals = [];
        }
        if (typeof this.entity.sealExemptionConfig.label !== 'string') {
            this.entity.sealExemptionConfig.label = '';
        }
        // Abre a configuração automaticamente se já houver selos configurados.
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

        // Flag de bloqueio server-side (spec §4.2). Fallback true somente quando
        // o backend ainda não expuser o campo — ambiente de desenvolvimento.
        canEdit() {
            return this.entity.canEditSealConfig !== false;
        },

        // Mapa id -> label para exibir os nomes dos selos selecionados nas tags.
        sealLabels() {
            const map = {};
            this.availableSeals.forEach((s) => {
                map[s.value] = s.label;
            });
            return map;
        },

        // IDs de selos configurados que não estão mais na lista de disponíveis
        // (ex.: selo desativado depois de configurado). Mantidos no read-only
        // para rastreabilidade, sinalizados visualmente.
        inactiveSelectedSeals() {
            const known = {};
            this.availableSeals.forEach((s) => { known[s.value] = true; });
            return (this.config?.seals || []).filter((id) => !known[id]);
        },

        selectedCount() {
            return (this.config?.seals || []).length;
        },
    },

    methods: {
        onToggleExpand(value) {
            if (!this.canEdit) {
                return;
            }
            this.expanded = value;
        },

        // mc-multiselect muta o array `model` in place (push/splice) e emite
        // 'selected'/'removed'. Usamos os eventos para disparar o salvamento.
        onSealSelected() {
            this.persist();
        },

        onSealRemoved() {
            this.persist();
        },

        onLabelChange() {
            this.persist(1500);
        },

        async removeAllSeals() {
            if (!this.canEdit) {
                return;
            }
            try {
                // Substitui o array para garantir reatividade e persistência.
                this.config.seals = [];
                await this.persist(0);
                this.expanded = false;
                this.messages.success(this.text('removeSuccess'));
            } catch (e) {
                this.messages.error(this.text('removeError'));
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
                    await this.entity.save();
                    this.$emit('changed', this.config);
                } catch (e) {
                    this.messages.error(e?.data?.message || this.text('saveError'));
                } finally {
                    this.saving = false;
                }
            }, delay);
        },
    },
});
