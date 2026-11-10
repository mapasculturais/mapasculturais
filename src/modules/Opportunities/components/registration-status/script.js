app.component('registration-status', {
    template: $TEMPLATES['registration-status'],

    props: {
        registration: {
            type: Entity,
            required: true
        },

        phase: {
            type: Entity,
            required: true
        }
    },
    
    setup(props, { slots }) {
        const hasSlot = name => !!slots[name];
        // os textos estão localizados no arquivo texts.php deste componente 
        const text = Utils.getTexts('registration-status');
        return { text, hasSlot }
    },

    data() {
        return {
            processing: false,
            entity: null,
            // R02 (#66): snapshot do resultado preliminar (#64) e flags de
            // publicação, quando o payload da fase não as carrega (risco D2).
            preliminarySnapshot: null,
            publishFlags: null,
        }
    },

    mounted() {
        this.loadPreliminaryData();
    },

    computed: {
        firstPhase() {
            return this.opportunity.parent || this.opportunity;
        },
        firstPhaseRegistration() {
            return $MAPAS.registrationPhases[this.firstPhase.id];
        },
        appealPhase() {
            return this.opportunity.isAppealPhase ? this.opportunity : this.opportunity.appealPhase;
        },

        appealRegistration() {
            const appealPhaseId = this.appealPhase?.id;
            if (!appealPhaseId) {
                return null;
            }

            return $MAPAS.registrationPhases[appealPhaseId] || this.entity;
        },

        /*
         * R02 (#66) — flags de publicação da fase principal.
         *
         * Risco D2 + lição do E2E: o item EMC do timeline é um objeto cru SEM
         * __objectType — o computed opportunity() resolve para o PRÓPRIO item
         * EMC, cujas flags de publicação estão aninhadas em item.opportunity
         * (OpportunityPhases/Module.php:927). Resolução multi-fonte: o
         * opportunity resolvido, o opportunity aninhado do item e o fetch
         * próprio (colunas públicas da API, legíveis pelo dono). Parse truthy
         * EXPLÍCITO — imune a 'false'/'0' serializados como string.
         */
        publishState() {
            const sources = [
                this.opportunity,
                this.phase?.opportunity,
                this.publishFlags,
            ].filter(Boolean);

            const isTruthyFlag = (value) => value === true || value === 1 || value === '1' || value === 'true';

            const flag = (key) => sources.some(source => isTruthyFlag(source[key]));

            return {
                final: flag('publishedRegistrations'),
                preliminary: flag('publishedPreliminaryRegistrations'),
            };
        },

        /**
         * Id da fase principal para o fetch das flags (risco D2): o
         * opportunity aninhado do item EMC, quando existir; senão o
         * opportunity resolvido pelo computed.
         */
        publishFlagsOpportunityId() {
            return this.phase?.opportunity?.id ?? this.opportunity?.id ?? null;
        },

        /**
         * Resultado visível ao proponente (mesma semântica server-side de
         * Opportunity::areRegistrationResultsPublished): final OU preliminar
         * (preliminar true implica two-stage ON por construção).
         */
        resultsPublished() {
            return this.publishState.final || this.publishState.preliminary;
        },

        /**
         * Snapshot do resultado preliminar (#64), exibido no box
         * "RESULTADO PRELIMINAR" quando há resultado publicado. Com final
         * publicado, o snapshot (se existir) permanece como histórico.
         */
        preliminarySnapshotValue() {
            if (!this.resultsPublished) {
                return null;
            }

            const snapshot = this.preliminarySnapshot?.preliminaryResultSnapshot;

            return (snapshot === undefined || snapshot === null || snapshot === '') ? null : String(snapshot);
        },

        canShowAppeal() {
            if (this.registration.opportunity.isReportingPhase) {
                return false;
            }

            if(!this.firstPhaseRegistration?.currentUserPermissions.create) {
                return false;
            }

            if(this.registration.opportunity.appealPhase && (this.appealRegistration || this?.registration?.opportunity?.appealPhase?.registrationFrom?.isFuture() || this?.registration?.opportunity?.appealPhase?.registrationTo?.isPast())) {
                return false;
            }

            /*
                R02 (#71) — decisão do dono 2026-09-22: o gatilho do recurso é
                EXCLUSIVAMENTE o resultado preliminar publicado (publishState,
                multi-fonte do #66). O status da inscrição deixa de ser
                condição — a JANELA da fase de recurso continua valendo acima.
            */
            return this.publishState.preliminary;
        },

        opportunity () {
            if (this.phase.__objectType === 'evaluationmethodconfiguration') {
                return this.phase.opportunity;
            } else {
                return this.phase;
            }
        },

        showRegistrationResults() {
            const { isReportingPhase, __objectType, publishEvaluationDetails } = this.phase;
            const { allow_proponent_response } = this.registration.opportunity;

            if (isReportingPhase === '1' && __objectType === 'opportunity' && allow_proponent_response) {
                return false;
            }

            return publishEvaluationDetails || allow_proponent_response;
        },

        statuses() {
            return this.registration.opportunity.statusLabels;
        },

        /*
         * R02 (#66): método da fase normalizado. No item EMC do timeline o
         * `type` vem da relação EvaluationMethodConfiguration->type
         * (jsonSerialize:449) serializada como OBJETO EntityType
         * ({id:'simple',...}) — comparações phase.type == 'simple' nunca
         * casavam e o bloco de resultado renderizava vazio. Normaliza
         * string|{id} para o slug do método.
         */
        phaseType() {
            const type = this.phase?.type;

            if (typeof type === 'string') {
                return type;
            }

            if (type && typeof type === 'object') {
                return type.id ?? type.slug ?? null;
            }

            return null;
        },
    },

    methods: {
        /**
         * R02 (#66): busca o snapshot do preliminar e, se necessário, as
         * flags de publicação (risco D2). Privacidade: o snapshot só é
         * buscado quando há resultado publicado (gate server-side do status);
         * leitura raw (populate do SDK descarta a chave virtual). Erros
         * deixam os boxes no estado vigente, nunca bloqueiam a página.
         */
        async loadPreliminaryData() {
            if (!this.registration?.id) {
                return;
            }

            try {
                // 1. Flags de publicação (risco D2): quando nenhum objeto do
                //    payload as carrega (nem o opportunity resolvido, nem o
                //    aninhado do item EMC), busca pelas colunas públicas.
                const hasFlag = (source) => !!source
                    && (source.publishedRegistrations !== undefined
                        || source.publishedPreliminaryRegistrations !== undefined);

                const needs_flags = !hasFlag(this.opportunity) && !hasFlag(this.phase?.opportunity);
                const flags_opportunity_id = this.publishFlagsOpportunityId;

                if (needs_flags && flags_opportunity_id) {
                    const opportunity_api = new API('opportunity');
                    const flags_rows = await opportunity_api.fetch('find', {
                        '@select': 'id,publishedRegistrations,publishedPreliminaryRegistrations',
                        'id': `EQ(${flags_opportunity_id})`,
                    }, { raw: true, rawProcessor: data => data });

                    this.publishFlags = flags_rows?.[0] || null;
                }

                // 2. Snapshot do preliminar (#64) — só quando há resultado
                //    publicado (publishState já considera o fetch acima);
                //    leitura raw (populate do SDK descarta a chave virtual).
                if (this.resultsPublished) {
                    const registration_api = new API('registration');
                    const snapshot_rows = await registration_api.fetch('find', {
                        '@select': 'id,preliminaryResultSnapshot',
                        'id': `EQ(${this.registration.id})`,
                    }, { raw: true, rawProcessor: data => data });

                    this.preliminarySnapshot = snapshot_rows?.[0] || null;
                }
            } catch (error) {
                console.error('registration-status:loadPreliminaryData', error);
            }
        },

        /**
         * R02 (#66): label do snapshot para qualificação (status do mapa:
         * valid/invalid — PRD CA-12: habilitada/inabilitada).
         */
        qualificationLabel(value) {
            if (value === 'valid') {
                return this.text('qualification valid');
            }

            if (value === 'invalid') {
                return this.text('qualification invalid');
            }

            return value ?? '—';
        },

        /**
         * R02 (#66): label do snapshot para o método simples (código do
         * status MIN → legenda oficial de status da inscrição).
         */
        simpleStatusLabel(code) {
            return this.statuses?.[String(code)] ?? this.statuses?.[parseInt(code, 10)] ?? code ?? '—';
        },

        /**
         * R02 (#66): cor do status do método simples — legenda oficial da
         * inscrição (docblock de getStatusDisplay: 10 verde, 8 laranja,
         * 3 vermelho, 2 roxo) no padrão do tema, igual ao verifyState do
         * appeal-phase-chat (classes utilitárias _atoms.scss).
         */
        simpleStatusColor(code) {
            return {
                '10': 'success__color',
                '8': 'warning__color',
                '3': 'danger__color',
                '2': 'danger__color',
            }[String(code)] ?? '';
        },

        /**
         * R02 (#66): cor do resultado de qualificação — Habilitada →
         * success; Inabilitada → danger (padrão do documental).
         */
        qualificationColor(value) {
            if (value === 'valid') {
                return 'success__color';
            }

            if (value === 'invalid') {
                return 'danger__color';
            }

            return '';
        },

        /**
         * R02 (#66): a box tem valor formatado por método? Quando true, o
         * resultado SUBSTITUI o mc-status genérico da fase (um único valor
         * por box — review do dono). Documental exige 1/-1 (ramo com
         * render); sem método/valor → false (status permanece).
         */
        hasMethodResult(value) {
            if (value === null || value === undefined || value === '') {
                return false;
            }

            switch (this.phaseType) {
                case 'simple':
                case 'technical':
                case 'qualification':
                    return true;
                case 'documentary':
                    return String(value) === '1' || String(value) === '-1';
                default:
                    return false;
            }
        },

        showPhaseDates() {
            const firstPhase = $MAPAS.opportunityPhases?.find((phase) => phase.isFirstPhase) || this.firstPhase;
            return !firstPhase?.hidePhaseDates;
        },

        shouldDisplayEvaluationResults(registration) {
            return $MAPAS.config.registrationResults.shouldDisplayEvaluationResults[registration.id];
        },
		formatNote(note) {
			note = parseFloat(note);
			return note.toLocaleString($MAPAS.config.locale);
		},
        /**
         * Retorna o status no formato esperado por <mc-status>,
         * respeitando a legenda oficial de cores da inscrição:
         * 0 Rascunho (preto), 1 Pendente (preto), 2 Inválida (roxo),
         * 3 Não selecionada (vermelho), 8 Suplente (laranja), 10 Selecionada (verde).
         */
        getStatusDisplay(registration) {
            let status = registration.status;
            if (registration.opportunity?.isAppealPhase) {
                status = this.shouldDisplayEvaluationResults(registration) ? status : 1;
            }

            return {
                key: status,
                value: status,
                label: this.showRegistrationStatus(registration),
            };
        },

        async createAppealPhaseRegistration() {
            this.processing = true;
            const messages = useMessages();
        
            const target = this.opportunity;

            const args = {
                registration_id: this.registration._id,
            };

            try {
                await target.POST('createAppealPhaseRegistration', {data: args, callback: (data) => {
                        this.entity = new Entity('registration');
                        this.entity.populate(data);
                        this.processing = false;
                        messages.success(this.text('Solicitação de recurso criada com sucesso'));

                        window.location.href = Utils.createUrl('registration', 'view', [this.entity.id]);
                }});
                    
            } catch (error) {
                console.error(error);
                messages.error(error.data ?? error);
            }
            this.processing = false;
        },

        fillFormButton() {
            window.location.href = this.appealRegistration.editUrl;
        },

        dateFrom() {
			if (this.appealPhase?.registrationFrom) {
				return this.appealPhase?.registrationFrom.date('2-digit year');
			}

			if (this.appealPhase?.evaluationMethodConfiguration?.evaluationFrom) {
				return this.appealPhase?.evaluationMethodConfiguration?.evaluationFrom.date('2-digit year');
			}
			return false;
		},

		dateTo() {
			if (this.appealPhase?.registrationTo) {
				return this.appealPhase?.registrationTo?.date('2-digit year');
			}

			if (this.appealPhase?.evaluationMethodConfiguration?.evaluationTo) {
				return this.appealPhase?.evaluationMethodConfiguration?.evaluationTo?.date('2-digit year');
			}
			return false;
		},

		hour() {
			if (this.appealPhase?.registrationTo) {
				return this.appealPhase?.registrationTo?.time();
			}
			if (this.appealPhase?.evaluationMethodConfiguration?.evaluationTo) {
				return this.appealPhase?.evaluationMethodConfiguration?.evaluationTo.time();
			}
			return false;
		},

        redirectToRegistrationForm() {
            return window.location.hash = "#ficha";
        },
        
        shouldShowResults(item) {
			// se é uma fase de avaliação que não tem uma fase de coleta de dados anterior
			const isEvaluation = item.__objectType == 'evaluationmethodconfiguration';

			// se é uma fase de coleta de dados que não tem uma fase de avaliação posterior
			const isRegistrationOnly = item.__objectType == 'opportunity' && !item.evaluationMethodConfiguration;

			const phaseOpportunity = item.__objectType == 'opportunity' ? item : item.opportunity;

			return phaseOpportunity.publishedRegistrations && (isRegistrationOnly || isEvaluation);
		
		},
        showResults(phase) {
            const types = ['qualification', 'technical', 'documentary'];
            // phaseType: normalização do type do item EMC (objeto EntityType).
            return types.includes(this.phaseType) || phase.publishEvaluationDetails;
        },

        showRegistrationStatus(registration) {
            if(registration.opportunity?.isReportingPhase) {
               return this.phase.opportunity.statusLabels[registration.status];
            }
            
            if(registration.opportunity.isAppealPhase) {
                return this.shouldDisplayEvaluationResults(registration) ? this.phase.appealPhase.statusLabels[registration.status] : this.phase.appealPhase.statusLabels[1];
            }

            if(registration.status == 0) {
                return this.statuses[registration.status] || this.text('Não enviada');
            }

            if(registration.status == 1) {
                return this.statuses[registration.status] || this.text('Enviada');
            }

            return this.statuses[registration.status];
        }
    }
});
