app.component('appeal-technical-score-correction', {
    template: $TEMPLATES['appeal-technical-score-correction'],

    props: {
        entity: {
            type: Entity,
            required: true,
        },
    },

    setup() {
        return {
            text: Utils.getTexts('appeal-technical-score-correction'),
            messages: useMessages(),
        };
    },

    data() {
        return {
            context: null,
            selections: {},
            reason: '',
            confirmNoScoreChange: false,
            selectedRelatorId: '',
            loading: true,
            submitting: false,
            conflict: false,
            error: '',
        };
    },

    computed: {
        canEditCorrection() {
            return this.context?.isRelator && (!this.context.current || this.context.current.status === 0);
        },

        draftVersion() {
            return this.context?.current?.status === 0 ? this.context.current.version : 0;
        },

        preview() {
            return this.context?.current?.preview || null;
        },

        history() {
            return this.context?.history || [];
        },

        hasFinalCorrection() {
            return this.context?.current && this.context.current.status !== 0;
        },
    },

    created() {
        this.loadContext();
    },

    methods: {
        async loadContext() {
            this.loading = true;
            this.error = '';
            try {
                const api = new API('registration');
                const response = await api.GET(this.entity.getUrl('technicalScoreCorrection'));
                if (!response.ok) {
                    throw await this.responseError(response);
                }
                this.context = await response.json();
                this.selectedRelatorId = this.context.relatorUserId || '';
                this.reason = this.context.current?.status === 0 ? this.context.current.reason : '';
                this.initializeSelections();
            } catch (error) {
                this.error = error.message || this.text('loadedError');
            } finally {
                this.loading = false;
            }
        },

        initializeSelections() {
            const existingItems = this.context?.current?.status === 0 ? this.context.current.items : [];
            const itemByEvaluation = Object.fromEntries((existingItems || []).map(item => [item.evaluationId, item]));
            this.selections = {};
            for (const evaluation of this.context?.evaluations || []) {
                const existing = itemByEvaluation[evaluation.id];
                const criteria = {};
                for (const criterion of this.context.criteria || []) {
                    const changed = existing?.changedCriteria?.[criterion.id];
                    criteria[criterion.id] = {
                        selected: Boolean(changed),
                        value: changed ? changed.after : evaluation.evaluationData[criterion.id],
                    };
                }
                this.selections[evaluation.id] = {
                    selected: Boolean(existing),
                    criteria,
                };
            }
        },

        async saveRelator() {
            if (!this.selectedRelatorId) return;
            await this.submit(
                'PATCH',
                this.entity.getUrl('technicalScoreCorrectionRelator'),
                { userId: Number(this.selectedRelatorId) },
                this.text('relatorSaved')
            );
        },

        buildDraftPayload() {
            const evaluations = [];
            for (const evaluation of this.context.evaluations) {
                const selection = this.selections[evaluation.id];
                if (!selection?.selected) continue;
                const criteria = {};
                for (const criterion of this.context.criteria) {
                    const selectedCriterion = selection.criteria[criterion.id];
                    if (selectedCriterion?.selected) {
                        criteria[criterion.id] = Number(selectedCriterion.value);
                    }
                }
                if (Object.keys(criteria).length) {
                    evaluations.push({ evaluationId: evaluation.id, criteria });
                }
            }
            return {
                expectedVersion: this.draftVersion,
                reason: this.reason,
                evaluations,
            };
        },

        async saveDraft() {
            if (!this.reason.trim()) {
                this.messages.error(this.text('requiredReason'));
                return;
            }
            const payload = this.buildDraftPayload();
            if (!payload.evaluations.length) {
                this.messages.error(this.text('selectEvaluation'));
                return;
            }
            await this.submit('PATCH', this.entity.getUrl('technicalScoreCorrection'), payload, this.text('saved'));
        },

        async resolveCorrection() {
            if (!this.reason.trim()) {
                this.messages.error(this.text('requiredReason'));
                return;
            }
            const evaluationVersions = {};
            for (const evaluation of this.context.evaluations) {
                evaluationVersions[evaluation.id] = evaluation.version;
            }
            await this.submit('POST', this.entity.getUrl('resolveTechnicalScoreCorrection'), {
                expectedVersion: this.draftVersion,
                evaluationVersions,
                reason: this.reason,
                confirmNoScoreChange: this.confirmNoScoreChange,
            }, this.confirmNoScoreChange ? this.text('noChangeResolved') : this.text('resolved'));
        },

        async reopenCorrection() {
            await this.submit('POST', this.entity.getUrl('reopenTechnicalScoreCorrection'), {}, this.text('reopened'));
        },

        async submit(method, url, payload, successMessage) {
            this.submitting = true;
            this.error = '';
            try {
                const api = new API('registration');
                const response = await api[method](url, payload);
                if (response.status === 409) {
                    this.conflict = true;
                    this.messages.error(this.text('conflict'));
                    await this.loadContext();
                    return;
                }
                if (!response.ok) {
                    throw await this.responseError(response);
                }
                this.conflict = false;
                this.messages.success(successMessage);
                await this.loadContext();
            } catch (error) {
                this.error = error.message || this.text('loadedError');
                this.messages.error(this.error);
            } finally {
                this.submitting = false;
            }
        },

        async responseError(response) {
            const body = await response.json().catch(() => ({}));
            return new Error(body.error || this.text('loadedError'));
        },

        statusLabel(status) {
            return ({
                0: 'Rascunho',
                1: 'Aplicada',
                2: 'Deferido sem alteração',
                '-1': 'Descartada',
            })[status] || status;
        },

        criterionLabel(entry, criterionId) {
            const criteria = entry?.criteriaConfigurationSnapshot?.criteria || [];
            return criteria.find(criterion => String(criterion.id) === String(criterionId))?.title || criterionId;
        },
    },
});
