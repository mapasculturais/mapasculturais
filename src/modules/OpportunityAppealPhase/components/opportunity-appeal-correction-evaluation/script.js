app.component('opportunity-appeal-correction-evaluation', {
    template: $TEMPLATES['opportunity-appeal-correction-evaluation'],

    props: {
        assignmentId: {
            type: [Number, String],
            required: true,
        },
    },

    setup() {
        const messages = useMessages();
        const text = Utils.getTexts('opportunity-appeal-correction-evaluation');

        return { messages, text };
    },

    data() {
        return {
            loading: true,
            environment: null,
            error: null,
            formData: {},
            savingDraft: false,
            sending: false,
            submitted: false,
            _committeeEvaluationEntities: {},
        };
    },

    computed: {
        isLocked() {
            if (this.submitted) {
                return true;
            }

            if (!this.environment) {
                return false;
            }

            if (this.environment.status === 'sent') {
                return true;
            }

            if (this.environment.deadline) {
                return new Date(this.environment.deadline) < new Date();
            }

            return false;
        },

        formattedDeadline() {
            if (!this.environment?.deadline) {
                return '';
            }

            return new Date(this.environment.deadline).toLocaleString('pt-BR');
        },

        criteriaList() {
            return this.environment?.criteria || [];
        },

        // F7 (#51): método da fase principal define o formulário renderizado
        method() {
            return this.environment?.method || 'technical';
        },

        isTechnical() {
            return this.method === 'technical';
        },

        isDocumentary() {
            return this.method === 'documentary';
        },

        isSimple() {
            return this.method === 'simple';
        },

        fieldsList() {
            return this.environment?.fields || [];
        },

        statusOptions() {
            return this.environment?.statusOptions || [];
        },

        totalScore() {
            let result = 0;

            for (const criterion of this.criteriaList) {
                const value = this.formData.data?.[criterion.id];

                if (value === null || value === undefined || value === '') {
                    continue;
                }

                result += this.toNumber(value) * this.toNumber(criterion.weight);
            }

            return parseFloat(result.toFixed(2));
        },

        originalOpinion() {
            const value = this.environment?.originalEvaluation?.obs;
            return (value === null || value === undefined || value === '') ? '' : String(value);
        },

        statusLabel() {
            const labels = {
                designated: this.text('status-designated'),
                draft: this.text('status-draft'),
                sent: this.text('status-sent'),
                reopened: this.text('status-reopened'),
            };

            return labels[this.environment?.status] || this.environment?.status || '';
        },

        correctionTypeLabel() {
            const labels = {
                official: this.text('correction-type-official'),
                record: this.text('correction-type-record'),
            };

            return labels[this.environment?.correctionType] || this.environment?.correctionType || '';
        },

        committeeRegistration() {
            const opinion = this.environment?.committeeOpinion;
            if (!opinion?.evaluationsDetails?.length) {
                return null;
            }

            return {
                id: opinion.registrationId,
                score: opinion.score ?? null,
                consolidatedResult: opinion.consolidatedResult ?? null,
                consolidatedDetails: opinion.consolidatedDetails || {},
                evaluationsDetails: opinion.evaluationsDetails,
            };
        },

        committeeOpinionMethod() {
            return this.environment?.committeeOpinion?.method || '';
        },

        committeeModalTitle() {
            const number = this.environment?.registrationNumber || '';
            const phase = this.environment?.opportunityName || this.text('committee-opinion-title');
            return number ? `${phase} - ${number}` : phase;
        },

        visibleForm() {
            return this.environment?.visibleForm || null;
        },

        hasVisibleFormContent() {
            const form = this.visibleForm;
            if (!form) {
                return false;
            }

            return !!(
                form.agentsSummary
                || form.spaceSummary?.length
                || form.fields?.length
                || form.files?.length
            );
        },

        registrationOwnerName() {
            return this.visibleForm?.agentsSummary?.owner?.name
                || this.visibleForm?.agentsSummary?.ownerSnapshot?.name
                || '';
        },

        registrationOwnerDescription() {
            return this.visibleForm?.agentsSummary?.ownerSnapshot?.shortDescription || '';
        },

        registrationOwnerAreas() {
            const areas = this.visibleForm?.agentsSummary?.ownerSnapshot?.terms?.area;
            if (!areas || !areas.length) {
                return '';
            }

            return Array.isArray(areas) ? areas.join(', ') : String(areas);
        },
    },

    mounted() {
        this.fetchEnvironment();
    },

    methods: {
        toNumber(value) {
            const number = Number(value);

            return Number.isFinite(number) ? number : 0;
        },

        committeeEvaluationEntity(evaluation) {
            if (!evaluation?.entityEvaluation) {
                return null;
            }

            const key = evaluation.id ?? evaluation.entityEvaluation.id;
            if (!key) {
                return null;
            }

            if (!this._committeeEvaluationEntities[key]) {
                const entity = new Entity('registrationevaluation');
                entity.populate(evaluation.entityEvaluation);
                this._committeeEvaluationEntities[key] = entity;
            }

            return this._committeeEvaluationEntities[key];
        },

        documentaryStatusColor(data) {
            switch (data) {
                case '1':
                case 'valid':
                    return 'success__color';
                case '-1':
                case 'invalid':
                default:
                    return 'danger__color';
            }
        },

        documentaryStatusLabel(data) {
            switch (data) {
                case '1':
                case 'valid':
                    return this.text('field-valid');
                case '-1':
                case 'invalid':
                default:
                    return this.text('field-invalid');
            }
        },

        /**
         * F7: garante a forma do formData por método — documental precisa de
         * {evaluation, obs} por campo liberado; técnico/simples precisam de obs.
         */
        initFormDataByMethod() {
            if (this.isDocumentary) {
                for (const field of this.fieldsList) {
                    const current = this.formData.data[field.id];

                    this.formData.data[field.id] = {
                        evaluation: '',
                        obs: '',
                        ...(current && typeof current === 'object' ? current : {}),
                    };
                }
            } else if (this.isSimple || this.isTechnical) {
                this.formData.data.status = this.formData.data.status ?? '';
                this.formData.data.obs = this.formData.data.obs ?? '';
            }
        },

        originalNote(criterion) {
            const value = this.environment?.originalEvaluation?.[criterion.id];

            return (value === null || value === undefined || value === '') ? '-' : value;
        },

        originalFieldEvaluation(field) {
            const value = this.environment?.originalEvaluation?.[field.id]?.evaluation;

            return this.fieldEvaluationLabel(value);
        },

        fieldEvaluationLabel(value) {
            if (value === 'valid') {
                return this.text('field-valid');
            }

            if (value === 'invalid') {
                return this.text('field-invalid');
            }

            return this.text('field-not-evaluated');
        },

        originalStatus() {
            const value = this.environment?.originalEvaluation?.status;
            const option = this.statusOptions.find(option => option.value === String(value ?? ''));

            return option ? option.label : (value ?? '—');
        },

        async fetchEnvironment() {
            this.loading = true;
            this.error = null;

            try {
                const api = new API();
                const url = Utils.createUrl('appealCorrector', 'environment', [this.assignmentId]);
                const res = await api.GET(url);

                if (res.ok) {
                    this.environment = await res.json();
                    this.formData.data = {
                        ...this.environment.originalEvaluation,
                        ...(this.environment.draft || {}),
                    };
                    this.initFormDataByMethod();
                } else {
                    this.error = {
                        status: res.status,
                        message: this.environmentErrorMessage(res.status),
                    };
                }
            } catch (e) {
                this.error = { status: 0, message: this.text('error-generic') };
            } finally {
                this.loading = false;
            }
        },

        environmentErrorMessage(status) {
            if (status === 403) {
                return this.text('error-forbidden');
            }

            if (status === 404) {
                return this.text('error-not-found');
            }

            return this.text('error-generic');
        },

        handleInput(criterion) {
            if (this.isLocked) {
                return;
            }

            const value = this.formData.data[criterion.id];

            if (value === '' || value === null || value === undefined) {
                return;
            }

            const max = this.toNumber(criterion.max);

            if (this.toNumber(value) > max) {
                this.messages.error(this.text('note-higher-configured'));
                this.formData.data[criterion.id] = max;
            } else if (value < 0) {
                this.formData.data[criterion.id] = 0;
            }
        },

        async extractErrorMessage(res) {
            try {
                const data = await res.json();

                if (data?.message) {
                    return data.message;
                }

                // errorJson() responds {error: true, data: <message>}
                if (data?.data) {
                    return typeof data.data === 'string' ? data.data : JSON.stringify(data.data);
                }
            } catch (e) {
                // body is not JSON, fall through to generic message
            }

            return this.text('error-generic');
        },

        async saveDraft() {
            if (this.savingDraft || this.isLocked) {
                return;
            }

            this.savingDraft = true;

            try {
                const api = new API('registrationevaluation');
                const url = api.createUrl('applyAppealCorrection', { id: this.environment.evaluationId });
                const res = await api.POST(url, {
                    evaluationData: { ...this.formData.data },
                    draft: true,
                });

                if (res.ok) {
                    const data = await res.json();
                    this.environment.draft = data.review?.correctedValue || { ...this.formData.data };
                    this.messages.success(this.text('draft-saved'));
                } else {
                    this.messages.error(await this.extractErrorMessage(res));
                }
            } catch (e) {
                this.messages.error(this.text('error-generic'));
            } finally {
                this.savingDraft = false;
            }
        },

        async sendCorrection() {
            if (this.sending || this.isLocked) {
                return;
            }

            this.sending = true;

            try {
                const api = new API('registrationevaluation');
                const url = api.createUrl('applyAppealCorrection', { id: this.environment.evaluationId });
                const res = await api.POST(url, {
                    evaluationData: { ...this.formData.data },
                    draft: false,
                });

                if (res.ok) {
                    this.submitted = true;
                    this.environment.status = 'sent';
                    this.messages.success(this.text('correction-sent'));
                } else {
                    this.messages.error(await this.extractErrorMessage(res));
                }
            } catch (e) {
                this.messages.error(this.text('error-generic'));
            } finally {
                this.sending = false;
            }
        },
    },
});
