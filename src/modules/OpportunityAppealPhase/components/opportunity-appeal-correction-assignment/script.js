/**
 * opportunity-appeal-correction-assignment (F2 #18 + F5 #45)
 *
 * Modal de designação de corretores por slot: lista as avaliações da fase
 * principal da inscrição (uma por avaliador), permite marcar quais notas serão
 * corrigidas e designar 1 corretor por slot marcado (dono do slot ou membro
 * da Comissão de Recursos — mesma regra de
 * RegistrationAppealReview::eligibleCorrectors()).
 *
 * F5: no painel de acompanhamento, designação ATIVA (status 0/1/3) ganha
 * "Substituir corretor" (PATCH single com {correctorUser}, CA-3 revalidado
 * server-side pelo PR6) e "Cancelar designação" (DELETE single — slot volta
 * a ser designável). Status ENVIADO (2) ganha "Reabrir correção" (POST
 * reopen/{id}), por slot. Correção já enviada alguma vez não é cancelável.
 *
 * Abertura: ver docblock do init.php deste componente (evento global
 * 'opportunity-appeal-correction-assignment:open' com {opportunity, registration}).
 *
 * Backend consumido (somente endpoints existentes):
 * - GET /api/registrationevaluation/find  → slots da inscrição.
 * - /registrationappealreview (API canônica da entidade) → criação e
 *   acompanhamento; habilitada somente quando 'endpointAvailable' (controller
 *   da entidade registrado no backend).
 * - PATCH/DELETE /registrationappealreview/single/{id} → substituição e
 *   cancelamento de designação (PR6 / issue #40).
 */

app.component('opportunity-appeal-correction-assignment', {
    template: $TEMPLATES['opportunity-appeal-correction-assignment'],

    emits: ['saved'],

    setup() {
        // os textos estão localizados no arquivo texts.php deste componente
        const text = Utils.getTexts('opportunity-appeal-correction-assignment');
        return { text };
    },

    data() {
        return {
            opportunityId: null,
            registrationId: null,
            registrationNumber: null,
            slots: [],
            reviews: [],
            loading: false,
            saving: false,
            formFieldsSearchQuery: '',
        };
    },

    computed: {
        config() {
            return $MAPAS.config.appealCorrectionAssignment || {};
        },

        endpointAvailable() {
            return this.config.endpointAvailable === true;
        },

        /**
         * Contexto elegível: fase técnica com fase de recurso ativa e Comissão
         * de Recursos injetada para a oportunidade recebida (espelha os gates
         * de RegistrationAppealReview::eligibleCorrectors()).
         */
        correctionEligible() {
            return this.appealPhaseId != null && Array.isArray(this.committee);
        },

        appealPhaseId() {
            const appeal_phases = this.config.appealPhases || {};
            return this.opportunityId != null
                ? appeal_phases[this.opportunityId] ?? null
                : null;
        },

        committee() {
            const committees = this.config.committees || {};
            return this.opportunityId != null
                ? committees[this.opportunityId] ?? null
                : null;
        },

        /**
         * F6 (#49): catálogo de critérios selecionáveis da fase principal
         * (injetado no init.php; presente só para technical/documentary).
         */
        criteriaCatalog() {
            const catalogs = this.config.criteriaCatalogs || {};
            return this.opportunityId != null
                ? catalogs[this.opportunityId] ?? null
                : null;
        },

        hasCriteriaSelector() {
            const catalog = this.criteriaCatalog;
            return !!catalog
                && (catalog.total ?? 0) > 0
                && this.allCriteriaIds().length > 0;
        },

        /**
         * Catálogo de campos do formulário liberáveis ao corretor
         * (agentsSummary, spaceSummary, campos e anexos das fases).
         */
        formFieldsCatalog() {
            const catalogs = this.config.formFieldsCatalogs || {};
            return this.opportunityId != null
                ? catalogs[this.opportunityId] ?? null
                : null;
        },

        hasFormFieldsSelector() {
            const catalog = this.formFieldsCatalog;
            return !!catalog
                && (catalog.total ?? 0) > 0
                && this.allFormFieldIds().length > 0;
        },

        /**
         * Lista filtrada no modal (mesmo filtro do fields-visible-evaluators:
         * título ou ID).
         */
        filteredFormFields() {
            const items = this.formFieldsCatalog?.items || [];
            const query = (this.formFieldsSearchQuery || '').toLowerCase().trim();

            if (!query) {
                return items;
            }

            return items.filter(item => {
                const label = String(item.label || '').toLowerCase();
                const id = String(item.id || '').toLowerCase();
                const numeric = item.numericId != null ? String(item.numericId) : '';

                return label.includes(query) || id.includes(query) || numeric.includes(query);
            });
        },

        markedSlots() {
            return this.slots.filter(slot => slot.checked);
        },

        hasInvalidSelection() {
            return this.markedSlots.some(slot => !this.normalizeId(slot.correctorUserId));
        },

        /**
         * F6: seleção de escopo vazia (nenhum critério) invalida o salvar
         * quando o seletor existe para o método da fase principal.
         */
        hasInvalidScopeSelection() {
            return this.hasCriteriaSelector
                && this.markedSlots.some(slot => this.scopeInvalidFrom(slot.scopeCriteria));
        },

        hasInvalidFormFieldsSelection() {
            return this.hasFormFieldsSelector
                && this.markedSlots.some(slot => this.formFieldsInvalidFrom(slot.formFields));
        },

        hasInvalidDeadline() {
            return this.markedSlots.some(slot => this.slotDeadlineError(slot));
        },

        canSave() {
            return this.endpointAvailable
                && this.correctionEligible
                && !this.loading
                && !this.saving
                && this.markedSlots.length > 0
                && !this.hasInvalidSelection
                && !this.hasInvalidScopeSelection
                && !this.hasInvalidFormFieldsSelection
                && !this.hasInvalidDeadline;
        },

        designatedCount() {
            return this.slots.filter(slot => this.reviewForSlot(slot) != null).length;
        },

        sentCount() {
            const sent_status = this.config.statusSent ?? 2;
            return this.reviews.filter(review => this.statusNumber(review) === sent_status).length;
        },

        progressSummary() {
            return this.text('progress summary')
                .replace('%s', this.designatedCount)
                .replace('%s', this.slots.length)
                .replace('%s', this.sentCount);
        },

        subtitle() {
            const number = this.registrationNumber || this.slots[0]?.registration?.number || null;
            return number ? `${this.text('registration')} ${number}` : '';
        },
    },

    mounted() {
        window.addEventListener('opportunity-appeal-correction-assignment:open', this.handleOpenEvent);
    },

    beforeUnmount() {
        window.removeEventListener('opportunity-appeal-correction-assignment:open', this.handleOpenEvent);
    },

    methods: {
        /**
         * Interface pública (F1): detail = {opportunity, registration}.
         * Aceita instâncias Entity do SDK ou objetos com ao menos {id}.
         */
        handleOpenEvent(event) {
            const { opportunity, registration } = event?.detail || {};

            const opportunity_id = this.normalizeId(opportunity);
            const registration_id = this.normalizeId(registration);

            if (!opportunity_id || !registration_id) {
                console.error('opportunity-appeal-correction-assignment: evento requer {opportunity, registration} com id válido');
                return;
            }

            this.opportunityId = opportunity_id;
            this.registrationId = registration_id;
            this.registrationNumber = registration?.number || null;
            this.slots = [];
            this.reviews = [];

            this.$refs.modal?.open();
            this.loadData();
        },

        async loadData() {
            this.loading = true;

            try {
                await Promise.all([
                    this.fetchSlots(),
                    this.endpointAvailable ? this.fetchReviews() : Promise.resolve(),
                ]);
            } catch (error) {
                console.error('opportunity-appeal-correction-assignment:', error);
            } finally {
                this.loading = false;
            }
        },

        /**
         * Lista as N avaliações da fase principal da inscrição (uma por avaliador),
         * via API de RegistrationEvaluation (a API filtra por permissão de visão).
         *
         * LEITURA RAW OBRIGATÓRIA: `fetch()` só ativa o modo raw com `raw: true`
         * nas options (rawProcessor sozinho é ignorado) — sem isso o payload
         * passa por Entity.populate(), que DESCARTA relações escalares
         * (user:35 vira undefined) e campos computados (resultString).
         *
         * Shapes reais da ApiQuery (evidência de rede): `user` volta ESCALAR
         * mesmo com select aninhado (expansão não suportada nesta entidade);
         * `registration` expande como objeto. Nomes de avaliadores vêm do mapa
         * `evaluators` injetado no init.php, não deste select.
         */
        async fetchSlots() {
            const api = new API('registrationevaluation');
            const evaluations = await api.fetch('find', {
                '@select': 'id,user,status,result,resultString,registration.{id,number}',
                'registration': `EQ(${this.registrationId})`,
                '@order': 'id ASC',
            }, { raw: true, rawProcessor: data => data });

            this.slots = (evaluations || []).map(evaluation => ({
                id: this.normalizeId(evaluation.id),
                user: evaluation.user,
                userId: this.normalizeId(evaluation.user),
                status: evaluation.status,
                result: evaluation.result,
                resultString: evaluation.resultString,
                registration: evaluation.registration,
                checked: false,
                correctorUserId: null,
                // F6: escopo inicia com TODOS os critérios (persiste null)
                scopeCriteria: this.hasCriteriaSelector ? this.allCriteriaIds() : null,
                scopeOpen: false,
                // Campos do formulário: todos liberados por padrão
                formFields: this.hasFormFieldsSelector ? this.allFormFieldIds() : null,
                formFieldsOpen: false,
                // CA-4: visibilidade no ambiente do corretor (defaults da designação)
                showAppealOpinion: true,
                showOriginalValuerName: true,
                endsAt: null,
                endsAtTimeSet: false,
                endsAtKey: 0,
                reopenOpen: false,
                reopenEndsAt: null,
                reopenEndsAtTimeSet: false,
                reopenEndsAtKey: 0,
                reopenWithoutDeadline: false,
                deadlineOpen: false,
                deadlineEndsAt: null,
                deadlineEndsAtTimeSet: false,
                deadlineEndsAtKey: 0,
                deadlineRemove: false,
            }));

            this.preSelectCorrectors();
        },

        /**
         * Acompanhamento: designações existentes da inscrição. Disponível
         * somente quando a API da entidade existe (endpointAvailable).
         * Leitura raw pelo mesmo motivo de fetchSlots: `originalEvaluation`
         * vem ACHATADO para escalar pela ApiQuery (evidência:
         * {"originalEvaluation":1}) e o populate do SDK o descartaria.
         */
        async fetchReviews() {
            if (!this.endpointAvailable) {
                return;
            }

            const api = new API('registrationappealreview');
            const reviews = await api.fetch('find', {
                '@select': 'id,originalEvaluation.id,status,correctionType,endsAt,sentTimestamp,correctorUser,releasedScope',
                'registration': `EQ(${this.registrationId})`,
                '@order': 'id ASC',
            }, { raw: true, rawProcessor: data => data });

            // normalizeId aceita escalar OU objeto — o join com os slots
            // compara ids normalizados nos dois lados. correctorUser (escalar)
            // alimenta o painel e o default da substituição (F5).
            this.reviews = (reviews || []).map(review => ({
                id: this.normalizeId(review.id),
                originalEvaluationId: this.normalizeId(review.originalEvaluation),
                status: review.status,
                correctionType: review.correctionType,
                endsAt: review.endsAt,
                sentTimestamp: review.sentTimestamp,
                correctorUserId: this.normalizeId(review.correctorUser),
                releasedScope: this.normalizeScope(review.releasedScope),
            }));
        },

        /**
         * Opções de corretor do slot: dono do slot + Comissão de Recursos,
         * sem duplicatas (mesma composição de eligibleCorrectors()).
         */
        correctorOptions(slot) {
            if (!this.correctionEligible) {
                return [];
            }

            const options = [];
            const seen = new Set();

            const owner_id = this.slotUserId(slot);
            if (owner_id != null) {
                options.push({
                    value: owner_id,
                    label: `${this.slotAgentName(slot)} (${this.text('slot owner tag')})`,
                });
                seen.add(owner_id);
            }

            for (const member of this.committee) {
                if (seen.has(member.userId)) {
                    continue;
                }

                options.push({
                    value: member.userId,
                    label: `${member.name} (${this.text('committee tag')})`,
                });
                seen.add(member.userId);
            }

            return options;
        },

        /**
         * Slot pode ser marcado: contexto elegível, sem designação ativa
         * (o índice único de slots ativos impõe a mesma regra no backend).
         */
        slotSelectable(slot) {
            return this.correctionEligible && !this.activeReviewForSlot(slot);
        },

        preSelectCorrectors() {
            for (const slot of this.slots) {
                slot.correctorUserId = this.slotUserId(slot);
            }
        },

        activeReviewForSlot(slot) {
            const active_statuses = this.config.activeStatuses || [0, 1, 3];
            return this.reviews.find(review =>
                review.originalEvaluationId != null
                && review.originalEvaluationId === this.normalizeId(slot.id)
                && active_statuses.includes(this.statusNumber(review))
            ) || null;
        },

        /**
         * Designação exibida no slot: a ativa, se houver; senão a mais
         * recente (reviews vêm ordenadas por id ASC).
         */
        reviewForSlot(slot) {
            const slot_reviews = this.reviews.filter(review =>
                review.originalEvaluationId != null
                && review.originalEvaluationId === this.normalizeId(slot.id)
            );

            return this.activeReviewForSlot(slot) || slot_reviews[slot_reviews.length - 1] || null;
        },

        statusNumber(review) {
            return parseInt(review?.status, 10);
        },

        statusLabel(review) {
            const status_key = {
                0: 'status designated',
                1: 'status draft',
                2: 'status sent',
                3: 'status reopened',
            }[this.statusNumber(review)];

            return status_key ? this.text(status_key) : String(review?.status ?? '');
        },

        /**
         * Classes utilitárias de cor do tema (0.settings/_atoms.scss) para o
         * rótulo e o ícone de status — mesmo padrão do appeal-phase-chat
         * (mc-icon "circle" + .{primary|success|warning|danger}__color):
         * designado=pendente (primary), rascunho=em andamento (warning),
         * enviado=concluído (success), reaberto=exige ação (danger).
         */
        statusClass(review) {
            return {
                0: 'primary__color',
                1: 'warning__color',
                2: 'success__color',
                3: 'danger__color',
            }[this.statusNumber(review)] || '';
        },

        reviewDeadline(review) {
            return this.formatDate(review?.endsAt);
        },

        reviewSentAt(review) {
            return this.formatDate(review?.sentTimestamp);
        },

        // ============================================================ //
        // F5 (#45): substituição e cancelamento de designação (CA-13)
        // ============================================================ //

        /**
         * Linha "trancada" (visual de inércia, cinza): designação sem nenhuma
         * ação disponível (nem gestão de ativa, nem reabertura de enviada).
         */
        isSlotLocked(slot) {
            const review = this.reviewForSlot(slot);
            return !!review && !this.canManageReview(review) && !this.canReopenReview(review);
        },

        reviewSentAtLabel(review) {
            return this.statusNumber(review) === (this.config.statusSent ?? 2)
                ? this.text('sent at')
                : this.text('last sent at');
        },

        /**
         * Papel do corretor designado para exibição estática na coluna
         * "Corretor designado": dono do slot ou Comissão de Recursos.
         */
        correctorRoleLabel(slot, review) {
            return this.normalizeId(review?.correctorUserId) === this.slotUserId(slot)
                ? this.text('slot owner tag')
                : this.text('committee tag');
        },

        // ============================================================ //
        // F6 (#49): seletor de escopo de critérios (Variante 3)
        // Semântica persistida: TODOS marcados → releasedScope null (sem
        // restrição, compatível com getReleasedCriteriaIds); subconjunto →
        // {criteria: [...]}; vazio → inválido (cliente bloqueia, backend 400).
        // ============================================================ //

        allCriteriaIds() {
            const catalog = this.criteriaCatalog;
            if (!catalog) {
                return [];
            }

            const ids = [];
            for (const section of catalog.sections || []) {
                for (const item of section.items || []) {
                    ids.push(item.id);
                }
            }

            return ids;
        },

        /**
         * Escopo persistido: critérios (quando restringidos) + campos do
         * formulário (quando restringidos) + flags de visibilidade (CA-4/CA-6).
         */
        scopePayloadFrom(selected, visibility = {}, formFields = null) {
            const scope = {
                showAppealOpinion: !!visibility.showAppealOpinion,
                showOriginalValuerName: !!visibility.showOriginalValuerName,
            };

            if (this.hasCriteriaSelector
                && selected
                && selected.length > 0
                && selected.length < this.allCriteriaIds().length
            ) {
                scope.criteria = [...selected];
            }

            if (this.hasFormFieldsSelector
                && formFields
                && formFields.length > 0
                && formFields.length < this.allFormFieldIds().length
            ) {
                scope.formFields = [...formFields];
            }

            return scope;
        },

        allFormFieldIds() {
            const catalog = this.formFieldsCatalog;
            if (!catalog?.items?.length) {
                return [];
            }

            return catalog.items.map(item => String(item.id));
        },

        formFieldsInvalidFrom(selected) {
            return this.hasFormFieldsSelector && (!selected || selected.length === 0);
        },

        slotFormFieldsCount(slot) {
            if (!this.hasFormFieldsSelector) {
                return 0;
            }

            return slot?.formFields?.length ?? this.formFieldsCatalog.total;
        },

        reviewFormFieldsLabel(review) {
            const ids = this.scopeFormFieldIds(review);

            if (ids == null) {
                return this.text('all form fields released');
            }

            const total = this.formFieldsCatalog?.total ?? ids.length;
            return this.text('form fields count')
                .replace('%s', ids.length)
                .replace('%s', total);
        },

        scopeFormFieldIds(review) {
            const scope = this.normalizeScope(review?.releasedScope);
            if (!scope) {
                return null;
            }

            if (!Object.prototype.hasOwnProperty.call(scope, 'formFields')) {
                return null;
            }

            const fields = scope.formFields;
            if (fields == null) {
                return null;
            }

            return Array.isArray(fields) ? fields.map(String) : null;
        },

        toggleSelectAllFormFields(slot, targetKey = 'formFields') {
            if (!this.hasFormFieldsSelector) {
                return;
            }

            const all = this.allFormFieldIds();
            const current = slot[targetKey] || [];
            slot[targetKey] = current.length === all.length ? [] : [...all];
        },

        formFieldIcon(item) {
            if (item?.kind === 'file' || String(item?.id || '').startsWith('rfc_')) {
                return 'file';
            }

            return 'text';
        },

        openFormFieldsModal(modal) {
            this.formFieldsSearchQuery = '';
            modal.open();
        },

        reviewShowsAppealOpinion(review) {
            const scope = this.normalizeScope(review?.releasedScope);
            return !!(scope && scope.showAppealOpinion);
        },

        reviewShowsOriginalValuerName(review) {
            const scope = this.normalizeScope(review?.releasedScope);
            if (!scope || !Object.prototype.hasOwnProperty.call(scope, 'showOriginalValuerName')) {
                return true;
            }

            return !!scope.showOriginalValuerName;
        },

        scopeInvalidFrom(selected) {
            return this.hasCriteriaSelector && (!selected || selected.length === 0);
        },

        slotScopeCount(slot) {
            if (!this.hasCriteriaSelector) {
                return 0;
            }

            return slot?.scopeCriteria?.length ?? this.criteriaCatalog.total;
        },

        /**
         * Escopo vigente da designação para o painel: "todos liberados"
         * (null) ou "X de Y critérios".
         */
        reviewScopeLabel(review) {
            const ids = this.scopeCriteriaIds(review);

            if (ids == null) {
                return this.text('all criteria released');
            }

            const total = this.criteriaCatalog?.total ?? ids.length;
            return this.text('scope count')
                .replace('%s', ids.length)
                .replace('%s', total);
        },

        scopeCriteriaIds(review) {
            const scope = this.normalizeScope(review?.releasedScope);
            if (!scope) {
                return null;
            }

            const criteria = scope.criteria ?? scope.fields ?? null;
            return criteria ? criteria.map(id => String(id)) : null;
        },

        normalizeScope(value) {
            if (value == null) {
                return null;
            }

            if (typeof value === 'string') {
                try {
                    value = JSON.parse(value);
                } catch (error) {
                    return null;
                }
            }

            return value && typeof value === 'object' ? value : null;
        },

        /**
         * Designação ATIVA (status 0/1/3) com API disponível pode ser
         * gerenciada da tela (substituir/cancelar). ENVIADO (2) não tem ações.
         */
        canManageReview(review) {
            if (!this.endpointAvailable || !review) {
                return false;
            }

            const active_statuses = this.config.activeStatuses || [0, 1, 3];
            return active_statuses.includes(this.statusNumber(review));
        },

        /**
         * Correção já enviada (sentTimestamp) guarda a nota original do slot:
         * o backend recusa o cancelamento, então a ação nem é oferecida.
         */
        canCancelReview(review) {
            return this.canManageReview(review) && !review.sentTimestamp;
        },

        /**
         * Correção ENVIADA (2) pode ser reaberta por slot, para o mesmo corretor.
         */
        canReopenReview(review) {
            return this.endpointAvailable
                && !!review
                && this.statusNumber(review) === (this.config.statusSent ?? 2);
        },

        startDeadlineEdit(slot) {
            this.clearSlotDeadline(slot, 'deadlineEndsAt');
            slot.deadlineRemove = false;
            slot.substitutionOpen = false;
            slot.deadlineOpen = true;
        },

        cancelDeadlineEdit(slot) {
            slot.deadlineOpen = false;
        },

        /**
         * Exige uma escolha: novo prazo futuro ou remover o prazo (só
         * oferecido quando há prazo vigente).
         */
        canConfirmDeadline(slot) {
            if (slot.changingDeadline) {
                return false;
            }

            if (slot.deadlineRemove) {
                return true;
            }

            return !!slot.deadlineEndsAt && !this.slotDeadlineError(slot, 'deadlineEndsAt');
        },

        /**
         * Altera o prazo da designação ativa do slot:
         * POST /registrationappealreview/deadline/{id} com {endsAt}
         * (null remove o prazo). O corretor é notificado pelo backend.
         */
        async confirmDeadline(slot) {
            const review = this.activeReviewForSlot(slot);
            if (!review || !this.canManageReview(review) || !this.canConfirmDeadline(slot)) {
                return;
            }

            const messages = useMessages();
            slot.changingDeadline = true;

            const api = new API('registrationappealreview');
            const url = Utils.createUrl('registrationappealreview', 'deadline', { id: review.id });
            const ends_at = slot.deadlineRemove ? null : this.toBackendDate(slot.deadlineEndsAt);

            try {
                const response = await api.POST(url, { endsAt: ends_at });

                if (!response.ok) {
                    throw await response.json().catch(() => ({}));
                }

                messages.success(this.text('deadline changed'));
                slot.deadlineOpen = false;
                await this.fetchReviews();
            } catch (error) {
                console.error('opportunity-appeal-correction-assignment:confirmDeadline', error);
                messages.error(this.backendErrorMessage(error) || this.text('deadline change error'));
            } finally {
                slot.changingDeadline = false;
            }
        },

        startReopen(slot) {
            this.clearSlotDeadline(slot, 'reopenEndsAt');
            slot.reopenWithoutDeadline = false;
            slot.reopenOpen = true;
        },

        cancelReopen(slot) {
            slot.reopenOpen = false;
        },

        reviewDeadlineExpired(review) {
            const raw = review?.endsAt && typeof review.endsAt === 'object'
                ? review.endsAt.date
                : review?.endsAt;

            return !!raw && new Date(raw) <= new Date();
        },

        /**
         * Prazo vencido exige novo prazo ou "sem prazo" (o backend recusa
         * reabrir com a janela já encerrada).
         */
        reopenDeadlineError(slot) {
            if (slot.reopenWithoutDeadline) {
                return null;
            }

            if (slot.reopenEndsAt) {
                return this.slotDeadlineError(slot, 'reopenEndsAt');
            }

            return this.reviewDeadlineExpired(this.reviewForSlot(slot))
                ? this.text('reopen expired deadline')
                : null;
        },

        canConfirmReopen(slot) {
            return !slot.reopening && !this.reopenDeadlineError(slot);
        },

        /**
         * Sem endsAt no payload o prazo atual é mantido; null remove o prazo.
         */
        reopenPayload(slot) {
            if (slot.reopenWithoutDeadline) {
                return { endsAt: null };
            }

            return slot.reopenEndsAt ? { endsAt: this.toBackendDate(slot.reopenEndsAt) } : {};
        },

        /**
         * Reabre a correção enviada do slot:
         * POST /registrationappealreview/reopen/{id}. Só este slot muda; o
         * backend valida status, prazo e conflito com designação ativa.
         */
        async reopenAssignment(slot) {
            const review = this.reviewForSlot(slot);
            if (!this.canReopenReview(review) || !this.canConfirmReopen(slot)) {
                return;
            }

            const messages = useMessages();
            slot.reopening = true;

            const api = new API('registrationappealreview');
            const url = Utils.createUrl('registrationappealreview', 'reopen', { id: review.id });

            try {
                const response = await api.POST(url, this.reopenPayload(slot));

                if (!response.ok) {
                    throw await response.json().catch(() => ({}));
                }

                messages.success(this.text('correction reopened'));
                slot.reopenOpen = false;
                await this.fetchReviews();
            } catch (error) {
                console.error('opportunity-appeal-correction-assignment:reopenAssignment', error);
                messages.error(this.backendErrorMessage(error) || this.text('reopen error'));
            } finally {
                slot.reopening = false;
            }
        },

        /**
         * Nome do corretor atual da designação (mesma resolução de nomes do
         * painel: evaluators → Comissão de Recursos → null).
         */
        reviewCorrectorName(review) {
            return this.evaluatorName(review?.correctorUserId);
        },

        startSubstitution(slot) {
            const review = this.activeReviewForSlot(slot);
            if (!review || !this.canManageReview(review)) {
                return;
            }

            slot.deadlineOpen = false;
            slot.substitutionOpen = true;
            // Default: o corretor atual (PATCH só é enviado se mudar).
            slot.substituteUserId = review.correctorUserId ?? this.slotUserId(slot);
            // F6: checklist pré-preenchida com o escopo vigente (todos quando
            // null); trocar para "todos marcados" volta a liberar integral.
            slot.substituteScopeCriteria = this.scopeCriteriaIds(review) ?? (this.hasCriteriaSelector ? this.allCriteriaIds() : null);
            slot.substituteScopeOpen = this.hasCriteriaSelector;
            slot.substituteFormFields = this.scopeFormFieldIds(review) ?? (this.hasFormFieldsSelector ? this.allFormFieldIds() : null);
            slot.substituteFormFieldsOpen = this.hasFormFieldsSelector;
            slot.substituteShowAppealOpinion = this.reviewShowsAppealOpinion(review);
            slot.substituteShowOriginalValuerName = this.reviewShowsOriginalValuerName(review);
        },

        cancelSubstitution(slot) {
            slot.substitutionOpen = false;
        },

        canConfirmSubstitution(slot) {
            return !slot.substituting
                && this.normalizeId(slot.substituteUserId) != null
                && !this.scopeInvalidFrom(slot.substituteScopeCriteria)
                && !this.formFieldsInvalidFrom(slot.substituteFormFields);
        },

        /**
         * Substitui o corretor da designação ativa do slot:
         * PATCH /registrationappealreview/single/{id} com {correctorUser}.
         * O backend revalida CA-3 (corretor inelegível → 400 com mensagem).
         * Sucesso → mensagem + fetchReviews() reflete o novo corretor sem reload.
         */
        async confirmSubstitution(slot) {
            const review = this.activeReviewForSlot(slot);
            if (!review || !this.canConfirmSubstitution(slot)) {
                return;
            }

            const messages = useMessages();
            slot.substituting = true;

            const api = new API('registrationappealreview');
            const url = Utils.createUrl('registrationappealreview', 'single', { id: review.id });

            try {
                const response = await api.PATCH(url, {
                    correctorUser: this.normalizeId(slot.substituteUserId),
                    releasedScope: this.scopePayloadFrom(
                        slot.substituteScopeCriteria,
                        {
                            showAppealOpinion: slot.substituteShowAppealOpinion,
                            showOriginalValuerName: slot.substituteShowOriginalValuerName,
                        },
                        slot.substituteFormFields,
                    ),
                });

                if (!response.ok) {
                    throw await response.json().catch(() => ({}));
                }

                messages.success(this.text('corrector replaced'));
                slot.substitutionOpen = false;
                await this.fetchReviews();
            } catch (error) {
                console.error('opportunity-appeal-correction-assignment:confirmSubstitution', error);
                messages.error(this.backendErrorMessage(error) || this.text('replace error'));
            } finally {
                slot.substituting = false;
            }
        },

        /**
         * Cancela (DELETE /registrationappealreview/single/{id}) a designação
         * do slot: volta ao estado designável — checkbox destravado pelo
         * recomputo de slotSelectable() após o fetchReviews().
         */
        async cancelAssignment(slot) {
            const review = this.activeReviewForSlot(slot) || this.reviewForSlot(slot);
            if (!review || !this.endpointAvailable) {
                return;
            }

            const messages = useMessages();
            slot.canceling = true;

            const api = new API('registrationappealreview');
            const url = Utils.createUrl('registrationappealreview', 'single', { id: review.id });

            try {
                const response = await api.DELETE(url);

                if (!response.ok) {
                    throw await response.json().catch(() => ({}));
                }

                messages.success(this.text('designation canceled'));
                await this.fetchReviews();
            } catch (error) {
                console.error('opportunity-appeal-correction-assignment:cancelAssignment', error);
                messages.error(this.backendErrorMessage(error) || this.text('cancel designation error'));
            } finally {
                slot.canceling = false;
            }
        },

        /**
         * Recebe os valores do mc-datepicker (datetime): emite o objeto de
         * hora {hours, minutes} ao mexer no relógio e, havendo dia, a Date
         * combinada. Só o dia escolhido chega como 00:00 — sem hora
         * informada, o prazo vale até 23:59 do dia.
         */
        setSlotDeadline(slot, value, field = 'endsAt') {
            if (value && !(value instanceof Date) && 'hours' in value) {
                slot[`${field}TimeSet`] = true;
                return;
            }

            if (!(value instanceof Date) || Number.isNaN(value.getTime())) {
                return;
            }

            const deadline = new Date(value);
            if (!slot[`${field}TimeSet`]) {
                deadline.setHours(23, 59, 0, 0);
            }

            slot[field] = deadline;
        },

        /**
         * Limpa o prazo e remonta o mc-datepicker (via key), que guarda o
         * valor digitado em estado interno.
         */
        clearSlotDeadline(slot, field = 'endsAt') {
            slot[field] = null;
            slot[`${field}TimeSet`] = false;
            slot[`${field}Key`] = (slot[`${field}Key`] || 0) + 1;
        },

        slotDeadlineError(slot, field = 'endsAt') {
            if (slot[field] && slot[field] <= new Date()) {
                return this.text('deadline in past');
            }

            return null;
        },

        /**
         * Data local 'AAAA-MM-DD HH:MM' (formato aceito pelo backend), sem
         * converter para UTC.
         */
        toBackendDate(date) {
            if (!(date instanceof Date)) {
                return null;
            }

            const pad = number => String(number).padStart(2, '0');

            return `${date.getFullYear()}-${pad(date.getMonth() + 1)}-${pad(date.getDate())} ${pad(date.getHours())}:${pad(date.getMinutes())}`;
        },

        formatDate(value) {
            if (!value) {
                return '';
            }

            const raw = typeof value === 'object' ? (value.date ?? null) : value;
            if (!raw) {
                return '';
            }

            try {
                const mcdate = new McDate(new Date(raw));
                return `${mcdate.date('2-digit year')} ${mcdate.time()}`;
            } catch (error) {
                return String(raw).slice(0, 16).replace('T', ' ');
            }
        },

        slotUserId(slot) {
            return slot?.userId ?? this.normalizeId(slot?.user);
        },

        /**
         * Nome do avaliador pelo mapa `evaluators` injetado no init.php —
         * chaveado por opportunityId (mesma estrutura de `committees`):
         * {opportunityId: {userId: name}}. A API de avaliação não expõe
         * nome (relação user não expande). Fallback: Comissão de Recursos
         * da fase de recurso (config.committees, também por oportunidade).
         */
        evaluatorName(userId) {
            if (userId == null) {
                return null;
            }

            const evaluators = this.config.evaluators || {};
            const name = this.opportunityId != null
                ? evaluators[this.opportunityId]?.[userId] ?? null
                : null;

            if (name) {
                return name;
            }

            const committee_member = (this.committee || []).find(member => member.userId === userId);
            return committee_member?.name || null;
        },

        slotAgentName(slot) {
            return this.evaluatorName(this.slotUserId(slot))
                || slot?.agent?.name
                || slot?.user?.profile?.name
                || slot?.user?.name
                || slot?.agent?.email
                || slot?.user?.profile?.email
                || this.text('slot owner tag');
        },

        /**
         * Cria 1 RegistrationAppealReview por nota marcada, via API canônica
         * da entidade. Defaults: status=DESIGNATED, correction_type=official,
         * prazo e escopo livres (CA-4 fica para a onda de parametrização).
         */
        async saveDesignations(close) {
            if (!this.canSave) {
                return;
            }

            const messages = useMessages();
            this.saving = true;

            const api = new API('registrationappealreview');
            const url = api.createUrl('index');

            try {
                for (const slot of this.markedSlots) {
                    const response = await api.POST(url, this.buildAssignmentPayload(slot));

                    if (!response.ok) {
                        const error = await response.json().catch(() => ({}));
                        throw error;
                    }
                }

                messages.success(this.text('saved'));
                this.$emit('saved', { registrationId: this.registrationId });

                await this.fetchReviews();

                close();
            } catch (error) {
                console.error('opportunity-appeal-correction-assignment:saveDesignations', error);
                messages.error(this.backendErrorMessage(error) || this.text('save error'));
            } finally {
                this.saving = false;
            }
        },

        /**
         * Extrai a mensagem de erro devolvida pelo backend para exibição.
         *
         * Shapes reais: Controller::errorJson responde
         * {error: true, data: "mensagem"} ou {error: true, data: {campo: msg}}
         * (validação); aceita também {error: "mensagem"} por defesa.
         * Retorna null quando não há texto utilizável (caller usa o genérico).
         */
        backendErrorMessage(error) {
            const candidates = [
                error?.data,
                error?.error,
                error?.data?.error,
            ];

            for (const candidate of candidates) {
                if (typeof candidate === 'string' && candidate.trim()) {
                    return candidate.trim();
                }

                if (candidate && typeof candidate === 'object') {
                    const messages = Object.values(candidate)
                        .flat()
                        .map(value => (typeof value === 'string' ? value.trim() : ''))
                        .filter(Boolean);

                    if (messages.length) {
                        return messages.join(' ');
                    }
                }
            }

            return null;
        },

        /**
         * Payload de criação de 1 designação (RegistrationAppealReview) para o
         * slot informado. Defaults: status=DESIGNATED, correction_type=official,
         * prazo e escopo livres (CA-4 fica para a onda de parametrização).
         */
        buildAssignmentPayload(slot) {
            return {
                originalEvaluation: slot.id,
                registration: this.registrationId,
                appealPhase: this.appealPhaseId,
                slotOwnerUser: this.slotUserId(slot),
                correctorUser: this.normalizeId(slot.correctorUserId),
                status: this.config.statusDesignated ?? 0,
                correctionType: this.config.correctionTypeDefault || 'official',
                // F6 + CA-4: critérios (se restritos) + visibilidade do ambiente
                releasedScope: this.scopePayloadFrom(
                    slot.scopeCriteria,
                    {
                        showAppealOpinion: slot.showAppealOpinion,
                        showOriginalValuerName: slot.showOriginalValuerName,
                    },
                    slot.formFields,
                ),
                // null = sem prazo (corretor pode corrigir a qualquer momento)
                endsAt: this.toBackendDate(slot.endsAt),
            };
        },

        normalizeId(value) {
            if (value == null) {
                return null;
            }

            const raw_id = typeof value === 'object' ? (value.id ?? null) : value;
            if (raw_id == null) {
                return null;
            }

            const parsed = parseInt(raw_id, 10);
            return Number.isNaN(parsed) ? null : parsed;
        },
    },
});
