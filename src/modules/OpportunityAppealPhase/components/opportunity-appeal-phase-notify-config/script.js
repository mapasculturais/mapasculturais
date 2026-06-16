/**
 * Componente opportunity-appeal-phase-notify-config
 *
 * Painel de gestão dos 5 fluxos de comunicação (e-mail + notificação do
 * sistema) da fase de recurso. Permite:
 *  - Ativar/desativar cada fluxo individualmente (toggle via entity-field
 *    com autosave, padrão do MapasCulturais).
 *  - Personalizar assunto e mensagem de cada fluxo via modal explícito
 *    com Save/Cancel e estado local (snapshot + dirty-state).
 *
 * As 15 metadata keys manipuladas seguem o contrato do backend:
 *   appealNotify_<flow>_{enabled,subject,message}
 * e todas são private => true (exigem permissão @control).
 *
 * Variáveis Mustache disponíveis por fluxo são entregues pelo backend em
 *   $MAPAS.config.opportunityAppealPhaseNotifyVariables[flowId]
 * como array de {key, label}.
 */

// Definição estática dos 5 fluxos canônicos (espelha Module::NOTIFY_FLOWS).
// O `label`/`recipients` são chaves de tradução resolvidas via this.text()
// no computed `flows` para manter o i18n consistente.
const NOTIFY_FLOW_DEFS = [
    { id: 'appealCreated',     label: 'Novo recurso aberto',           recipients: 'Proponente e Gestores' },
    { id: 'appealSent',        label: 'Recurso pronto para avaliar',   recipients: 'Avaliadores' },
    { id: 'statusNotApproved', label: 'Recurso negado',                recipients: 'Proponente' },
    { id: 'statusApproved',    label: 'Recurso aceito',                recipients: 'Proponente' },
    { id: 'statusInvalid',     label: 'Recurso invalidado',            recipients: 'Proponente' },
];

app.component('opportunity-appeal-phase-notify-config', {
    template: $TEMPLATES['opportunity-appeal-phase-notify-config'],

    setup() {
        const text = Utils.getTexts('opportunity-appeal-phase-notify-config');
        return { text };
    },

    props: {
        // A fase de recurso (Opportunity filha) onde vivem as 15 keys.
        entity: {
            type: Entity,
            required: true,
        },
        // Aba atual do componente pai ('config' | 'registrations').
        tab: {
            type: String,
            default: 'config',
        },
    },

    data() {
        return {
            // Estado exclusivo do modal — nenhum estado dos toggles vive
            // aqui, pois estes são persistidos via entity-field autosave.
            modalOpen: false,
            activeFlow: null,        // flow ID em edição no modal
            draftSubject: '',        // buffer do campo Assunto
            draftMessage: '',        // buffer do campo Mensagem
            savedSnapshot: {         // clone congelado na abertura do modal
                subject: '',
                message: '',
            },
            processing: false,       // loading do botão Salvar
            // Rastreia qual input do modal recebeu foco por último, para que
            // insertVariable() saiba onde injetar a variável. Não reativo
            // por design (não alimenta bindings no template).
            lastFocusedField: 'subject',
        };
    },

    computed: {
        /**
         * Lista de 5 fluxos com labels traduzidos e nomes de props
         * derivados do contrato `appealNotify_<flow>_{enabled,subject,message}`.
         */
        flows() {
            return NOTIFY_FLOW_DEFS.map((def) => ({
                id: def.id,
                label: this.text(def.label),
                recipients: this.text(def.recipients),
                enabledProp: `appealNotify_${def.id}_enabled`,
                subjectProp: `appealNotify_${def.id}_subject`,
                messageProp: `appealNotify_${def.id}_message`,
            }));
        },

        /**
         * Quantos dos 5 fluxos estão ativos (entidade persiste '1' como true).
         */
        activeFlowCount() {
            if (!this.entity) return 0;
            return this.flows.filter((f) => this.isFlowOn(f)).length;
        },

        /**
         * Classe do badge "X de 5":
         *   0     -> warning (cor de atenção)
         *   1..4  -> neutra
         *   5     -> success
         */
        badgeClass() {
            if (this.activeFlowCount === 0) return 'opportunity-appeal-phase-notify-config__badge--warning';
            if (this.activeFlowCount === 5) return 'opportunity-appeal-phase-notify-config__badge--success';
            return 'opportunity-appeal-phase-notify-config__badge--neutral';
        },

        /**
         * Título contextual do modal: "Personalizar — <label do fluxo>".
         */
        modalTitle() {
            if (!this.activeFlow) return this.text('Personalizar notificação');
            const flow = this.flows.find((f) => f.id === this.activeFlow);
            return this.text('Personalizar') + ' — ' + (flow?.label || '');
        },

        /**
         * Estado de "sujo": compara os drafts com o snapshot da abertura.
         * Desabilita fechamentos rápidos (esc/click fora) e habilita Salvar.
         */
        isDirty() {
            if (!this.activeFlow) return false;
            return this.draftSubject !== this.savedSnapshot.subject
                || this.draftMessage !== this.savedSnapshot.message;
        },

        /**
         * Variáveis Mustache disponíveis para o fluxo em edição no modal,
         * entregues pelo backend em $MAPAS.config.opportunityAppealPhaseNotifyVariables.
         */
        activeFlowVariables() {
            if (!this.activeFlow) return [];
            const vars = $MAPAS?.config?.opportunityAppealPhaseNotifyVariables?.[this.activeFlow];
            return Array.isArray(vars) ? vars : [];
        },

        /**
         * Selo do modal: "Personalizado" se subject/message não-nulos; senão "Texto padrão".
         */
        activeFlowIsCustom() {
            return this.activeFlow ? this.hasCustomText(this.activeFlow) : false;
        },

        /**
         * Indicador reativo do rodapé do modal — lê diretamente da entidade
         * (não do draft, pois o toggle é autosave externo).
         */
        activeFlowEnabled() {
            if (!this.activeFlow) return false;
            const flow = this.flows.find((f) => f.id === this.activeFlow);
            return flow ? this.isFlowOn(flow) : false;
        },
    },

    methods: {
        /**
         * Verifica se um fluxo está ligado, tratando null como false
         * (fases existentes em produção nunca tiveram a flag escrita).
         */
        isFlowOn(flow) {
            if (!this.entity) return false;
            const v = this.entity[flow.enabledProp];
            return v === true || v === '1' || v === 1;
        },

        /**
         * Verifica se um fluxo possui texto customizado (subject ou message
         * não-null e não-vazio). Usado para o selo "Personalizado".
         */
        hasCustomText(flowId) {
            if (!this.entity) return false;
            const flow = this.flows.find((f) => f.id === flowId);
            if (!flow) return false;
            const s = this.entity[flow.subjectProp];
            const m = this.entity[flow.messageProp];
            return !!((s !== null && s !== undefined && String(s).trim() !== ''))
                || !!((m !== null && m !== undefined && String(m).trim() !== ''));
        },

        /**
         * Abre o modal para um fluxo. Copia os valores persistidos para os
         * drafts, congela um snapshot para comparação (isDirty), foca o
         * campo assunto.
         */
        openModal(flowId) {
            const flow = this.flows.find((f) => f.id === flowId);
            if (!flow || !this.entity) {
                this.toast(this.text('Não foi possível abrir o formulário'), 'error');
                return;
            }
            this.activeFlow = flowId;
            // null -> string vazia para o input funcionar; vira null ao salvar.
            this.draftSubject = this.entity[flow.subjectProp] ?? '';
            this.draftMessage = this.entity[flow.messageProp] ?? '';
            this.savedSnapshot = {
                subject: this.draftSubject,
                message: this.draftMessage,
            };
            this.processing = false;
            this.lastFocusedField = 'subject';

            // Abre o modal programaticamente e só então foca o input.
            // mc-modal pode ser aberto via $refs.editModal.open().
            this.$refs.editModal?.open();
            this.modalOpen = true;

            this.$nextTick(() => {
                this.$refs.subjectInput?.focus();
            });
        },

        /**
         * Persiste os drafts de volta na entidade e chama entity.save().
         * Em caso de erro, mantém o modal aberto para o usuário corrigir.
         * Strings vazias -> null (recua ao fallback i18n no backend).
         */
        async saveModal() {
            if (!this.activeFlow || this.processing) return;
            if (!this.isDirty) {
                this.toast(this.text('Nenhuma alteração a salvar'), 'info');
                return;
            }
            const flow = this.flows.find((f) => f.id === this.activeFlow);
            if (!flow) return;

            this.processing = true;
            try {
                // Sanitização defensiva de quebra de linha no subject (header injection).
                // Remove CR, LF e byte NUL. O backend também aplica a mesma defesa em
                // resolveText() para %0A/%0D — aqui fokamos nos caracteres reais que
                // um input de texto pode capturar.
                const subject = (this.draftSubject || '')
                    .replace(/[\r\n\u0000]/g, '')
                    .trim();
                const message = (this.draftMessage || '').trim();

                this.entity[flow.subjectProp] = subject === '' ? null : subject;
                this.entity[flow.messageProp] = message === '' ? null : message;

                // Força estas chaves no payload mesmo se __originalValues
                // tiver o mesmo valor (cobre edge cases de cache).
                this._markChanged(flow.subjectProp);
                this._markChanged(flow.messageProp);

                await this.entity.save(0);

                // Atualiza snapshot para desligar o dirty-state.
                this.savedSnapshot = {
                    subject: this.draftSubject,
                    message: this.draftMessage,
                };

                this.toast(this.text('Configuração de notificação salva'), 'success');
                this.$refs.editModal?.close();
                this.modalOpen = false;
            } catch (error) {
                this.toast(this.text('Erro ao salvar a configuração'), 'error');
                // Mantém o modal aberto para o usuário tentar novamente.
            } finally {
                this.processing = false;
            }
        },

        /**
         * Cancela: reverte os drafts ao snapshot e fecha o modal.
         */
        cancelModal() {
            this.draftSubject = this.savedSnapshot.subject;
            this.draftMessage = this.savedSnapshot.message;
            this.$refs.editModal?.close();
            this.modalOpen = false;
        },

        /**
         * Limpa drafts para null (vira null ao salvar). Não fecha o modal:
         * o usuário precisa confirmar em "Salvar".
         */
        restoreDefault() {
            this.draftSubject = '';
            this.draftMessage = '';
            this.toast(this.text('Texto restaurado para o padrão'), 'info');
            this.$nextTick(() => {
                this.$refs.subjectInput?.focus();
            });
        },

        /**
         * Injeta a variável Mustache {{key}} no campo atualmente focado
         * (Assunto ou Mensagem) na posição do cursor. Não usa document.execCommand
         * (deprecated): manipula diretamente selectionStart/selectionEnd do input.
         */
        insertVariable(key) {
            const token = `{{${key}}}`;
            const refName = this.lastFocusedField === 'message' ? 'messageInput' : 'subjectInput';
            const input = this.$refs[refName];

            if (!input) {
                // Fallback: appenda ao assunto.
                this.draftSubject = (this.draftSubject || '') + token;
                return;
            }

            const isMessage = this.lastFocusedField === 'message';
            const current = isMessage ? (this.draftMessage || '') : (this.draftSubject || '');

            const start = (typeof input.selectionStart === 'number') ? input.selectionStart : current.length;
            const end = (typeof input.selectionEnd === 'number') ? input.selectionEnd : start;

            const newValue = current.slice(0, start) + token + current.slice(end);
            if (isMessage) {
                this.draftMessage = newValue;
            } else {
                this.draftSubject = newValue;
            }

            // Reposiciona o cursor logo após o token inserido.
            this.$nextTick(() => {
                const pos = start + token.length;
                input.focus();
                try {
                    input.setSelectionRange(pos, pos);
                } catch (e) {
                    // Alguns navegadores podem falhar setSelectionRange em textareas hidden; ignore.
                }
            });
        },

        /**
         * Handler foco dos inputs do modal — rastreia qual campo recebeu
         * foco por último para que insertVariable saiba onde injetar.
         */
        onFocusField(which) {
            this.lastFocusedField = which === 'message' ? 'message' : 'subject';
        },

        /**
         * Handler do evento @change do entity-field (toggle) — fornece
         * feedback de toast confirmando a transição.
         *
         * O autosave do entity-field já dispara entity.save() por conta
         * própria; aqui só reverberamos o resultado como toast.
         *
         * @param {Object} flow Definição do fluxo (de `flows`).
         * @param {Object} payload Payload do entity-field: {entity, prop, oldValue, newValue}.
         */
        onToggle(flow, payload) {
            // payload.newValue é o novo estado do checkbox (booleano direto do
            // event.target.checked, conforme entity-field.js:363).
            const isOn = payload?.newValue === true;
            this.toast(
                isOn ? this.text('Notificação ativada') : this.text('Notificação desativada'),
                'success'
            );
        },

        /**
         * Helper: marca uma chave como modificada na entidade, para que
         * data(true) a inclua no payload mesmo quando __originalValues
         * porventura já tenha o mesmo valor (edge case após restores).
         */
        _markChanged(prop) {
            if (!this.entity) return;
            if (!this.entity.__changedKeys) {
                this.entity.__changedKeys = [];
            }
            if (!this.entity.__changedKeys.includes(prop)) {
                this.entity.__changedKeys.push(prop);
            }
        },

        /**
         * Wrapper de toast com nível. O useMessages() do MapasCulturais
         * expõe success/error/info/warn.
         */
        toast(message, level = 'success') {
            if (!message) return;
            const messages = useMessages();
            const fn = messages[level] || messages.success;
            if (typeof fn === 'function') {
                fn.call(messages, message);
            }
        },
    },
});
