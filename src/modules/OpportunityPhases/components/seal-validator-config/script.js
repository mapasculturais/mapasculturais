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
            // Condicionalidade: selo atualmente expandido para editar condições
            expandedConditionSeal: null,
            // Rascunho local de conditions (inclui cláusulas incompletas).
            // Nunca vai para o Entity até estar completo — o backend rejeita
            // field/values vazios e o PATCH falho corrompe __validationErrors.
            conditionsDraft: {},
        };
    },

    beforeMount() {
        this.ensureConfigStructure();
        this.syncConditionsDraftFromEntity();
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

        // --- Condicionalidade (spec-fe9b2cfc) ---

        conditionalFields() {
            // Preferir registrationFilterFields (fonte): evita depender da ordem do init.php
            // entre registration-distribution-rule e seal-validator-config.
            const fromDistribution = $MAPAS?.config?.registrationFilterFields;
            if (Array.isArray(fromDistribution) && fromDistribution.length) {
                return fromDistribution;
            }
            return $MAPAS?.config?.sealValidatorConfig?.conditionalFields || [];
        },

        // Invalidadores de um selo (vem do availableSeals[].invalidators)
        invalidatorsBySeal() {
            return (sealId) => {
                const seal = this.sealById[sealId] || this.sealById[String(sealId)];
                return seal?.invalidators || [];
            };
        },

        // Condições de um selo específico (objeto invalidatorKey → { clauses })
        conditionsForSeal() {
            return (sealId) => {
                const key = String(sealId);
                return this.conditionsDraft[key] || {};
            };
        },

        // Um selo tem condições configuradas?
        sealHasConditions() {
            return (sealId) => {
                const conds = this.conditionsForSeal(sealId);
                return Object.keys(conds).length > 0;
            };
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
                // Preserva conditions ao renormalizar seals
                const preservedConditions = this.entity.sealExemptionConfig.conditions;
                this.entity.sealExemptionConfig = preservedConditions
                    ? { seals: normalized, conditions: preservedConditions }
                    : { seals: normalized };
            }
        },

        /**
         * Atribui novo objeto ao metadado para que o Entity.data() detecte mudança.
         * Só envia conditions com cláusulas completas (field + values).
         */
        assignSealConfig(seals) {
            const normalized = this.normalizeSealIds(seals);
            this.pruneConditionsDraftToSeals(normalized);
            const conditions = this.sanitizeConditionsForSave(this.conditionsDraft);
            this.entity.sealExemptionConfig = conditions
                ? { seals: normalized, conditions }
                : { seals: normalized };
        },

        syncConditionsDraftFromEntity() {
            const saved = this.entity.sealExemptionConfig?.conditions;
            this.conditionsDraft = saved && typeof saved === 'object'
                ? JSON.parse(JSON.stringify(saved))
                : {};
        },

        pruneConditionsDraftToSeals(seals) {
            const allowed = new Set(this.normalizeSealIds(seals).map(String));
            Object.keys(this.conditionsDraft).forEach((key) => {
                if (!allowed.has(key)) {
                    delete this.conditionsDraft[key];
                }
            });
        },

        isCompleteClause(clause) {
            return !!(
                clause
                && typeof clause.field === 'string'
                && clause.field !== ''
                && Array.isArray(clause.values)
                && clause.values.length > 0
            );
        },

        /**
         * Remove rascunhos incompletos antes do PATCH (backend exige field/values).
         */
        sanitizeConditionsForSave(conditions) {
            if (!conditions || typeof conditions !== 'object') {
                return undefined;
            }
            const out = {};
            Object.entries(conditions).forEach(([sealId, invalidators]) => {
                if (!invalidators || typeof invalidators !== 'object') {
                    return;
                }
                const cleanInvalidators = {};
                Object.entries(invalidators).forEach(([invKey, condDef]) => {
                    const clauses = (condDef?.clauses || [])
                        .filter((c) => this.isCompleteClause(c))
                        .map((c) => ({ field: c.field, values: [...c.values] }));
                    if (clauses.length) {
                        cleanInvalidators[invKey] = { clauses };
                    }
                });
                if (Object.keys(cleanInvalidators).length) {
                    out[sealId] = cleanInvalidators;
                }
            });
            return Object.keys(out).length ? out : undefined;
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
                this.conditionsDraft = {};
                this.assignSealConfig([]);
                await this.persist(0);
                this.expanded = false;
                this.messages.success(this.text('removeSuccess'));
            } catch (e) {
                this.messages.error(e?.data?.message || this.text('removeError'));
            }
        },

        // --- Condicionalidade (spec-fe9b2cfc) ---

        ensureDraftSeal(sealId) {
            const key = String(sealId);
            if (!this.conditionsDraft[key] || typeof this.conditionsDraft[key] !== 'object') {
                this.conditionsDraft[key] = {};
            }
            return this.conditionsDraft[key];
        },

        /**
         * Opções de valores para um campo do formulário, por tipo.
         * appliedForQuota → Sim/Não (values 1/0); demais checkboxes → Marcado/Desmarcado.
         */
        getFieldOptions(field) {
            if (!field) return [];
            const type = field.fieldType;
            if (type === 'checkbox') {
                if (field.fieldName === 'appliedForQuota') {
                    return [
                        { value: '1', label: this.text('conditionSim') },
                        { value: '0', label: this.text('conditionNao') },
                    ];
                }
                return [
                    { value: '1', label: this.text('conditionMarcado') },
                    { value: '0', label: this.text('conditionDesmarcado') },
                ];
            }
            const opts = field.fieldOptions || [];
            if (Array.isArray(opts)) {
                return opts.map((o) => {
                    const value = String(o);
                    // Opções de taxonomia/agent costumam vir como ":Não informado"
                    const label = value.startsWith(':') ? value.slice(1) : value;
                    return { value, label };
                });
            }
            return [];
        },

        /**
         * mc-select emite { text, value } em change-option (não usa v-model:model).
         * Só atualiza o rascunho — persistir com values=[] falharia no backend.
         */
        onClauseFieldChange(sealId, invalidatorKey, clauseIndex, option) {
            if (!this.canEdit) return;
            const cond = this.conditionsDraft[String(sealId)]?.[invalidatorKey];
            if (!cond || !cond.clauses[clauseIndex]) return;
            const value = option?.value ?? option ?? '';
            cond.clauses[clauseIndex].field = value;
            cond.clauses[clauseIndex].values = [];
            // Sem persist: cláusula incompleta até o usuário marcar valores.
        },

        /**
         * Adiciona uma cláusula em rascunho (não persiste até field+values completos).
         */
        addCondition(sealId, invalidatorKey) {
            if (!this.canEdit) return;
            const sealConds = this.ensureDraftSeal(sealId);
            if (!sealConds[invalidatorKey]) {
                sealConds[invalidatorKey] = { clauses: [] };
            }
            sealConds[invalidatorKey].clauses.push({ field: '', values: [] });
        },

        /**
         * Remove uma condição inteira de um invalidador.
         */
        removeCondition(sealId, invalidatorKey) {
            if (!this.canEdit) return;
            const key = String(sealId);
            if (this.conditionsDraft[key]?.[invalidatorKey]) {
                delete this.conditionsDraft[key][invalidatorKey];
                if (Object.keys(this.conditionsDraft[key]).length === 0) {
                    delete this.conditionsDraft[key];
                }
                this.persist();
            }
        },

        /**
         * Remove uma cláusula específica.
         */
        removeClause(sealId, invalidatorKey, clauseIndex) {
            if (!this.canEdit) return;
            const key = String(sealId);
            const cond = this.conditionsDraft[key]?.[invalidatorKey];
            if (cond && cond.clauses) {
                const wasComplete = this.isCompleteClause(cond.clauses[clauseIndex]);
                cond.clauses.splice(clauseIndex, 1);
                if (cond.clauses.length === 0) {
                    this.removeCondition(sealId, invalidatorKey);
                } else if (wasComplete) {
                    // Só persiste se removeu algo que já estava no servidor.
                    this.persist();
                }
            }
        },

        /**
         * Toggle de um valor aceito numa cláusula (select/checkboxes).
         */
        toggleConditionValue(sealId, invalidatorKey, clauseIndex, value) {
            if (!this.canEdit) return;
            const cond = this.conditionsDraft[String(sealId)]?.[invalidatorKey];
            if (!cond || !cond.clauses[clauseIndex]) return;
            const values = cond.clauses[clauseIndex].values;
            const idx = values.indexOf(value);
            if (idx >= 0) {
                values.splice(idx, 1);
            } else {
                values.push(value);
            }
            // Persiste só com cláusula completa; senão sanitize omite o rascunho.
            if (this.isCompleteClause(cond.clauses[clauseIndex]) || values.length === 0) {
                this.persist();
            }
        },

        /**
         * Define valor único (checkbox/radio).
         */
        setConditionSingleValue(sealId, invalidatorKey, clauseIndex, value) {
            if (!this.canEdit) return;
            const cond = this.conditionsDraft[String(sealId)]?.[invalidatorKey];
            if (!cond || !cond.clauses[clauseIndex]) return;
            cond.clauses[clauseIndex].values = [value];
            if (this.isCompleteClause(cond.clauses[clauseIndex])) {
                this.persist();
            }
        },

        /**
         * Preview textual de uma cláusula.
         */
        clausePreview(clause) {
            if (!clause || !clause.field) return '';
            const field = this.conditionalFields.find((f) => f.fieldName === clause.field);
            const label = field?.title || clause.field;
            const valLabels = (clause.values || []).map((v) => {
                if (!field || field.fieldType !== 'checkbox') return v;
                if (field.fieldName === 'appliedForQuota') {
                    return v === '1' ? this.text('conditionSim') : this.text('conditionNao');
                }
                return v === '1' ? this.text('conditionMarcado') : this.text('conditionDesmarcado');
            });
            return `${label} = ${valLabels.join(' OU ')}`;
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
                    // Rascunhos incompletos ficam só em conditionsDraft (já preservados).
                    this.$emit('changed', this.entity.sealExemptionConfig);
                } catch (e) {
                    const msg = typeof e?.data === 'string'
                        ? e.data
                        : (e?.data?.message || this.text('saveError'));
                    this.messages.error(msg);
                } finally {
                    this.saving = false;
                }
            }, delay);
        },
    },
});
