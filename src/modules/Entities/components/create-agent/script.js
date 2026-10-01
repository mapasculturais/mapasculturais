app.component('create-agent', {
    template: $TEMPLATES['create-agent'],
    emits: ['create'],

    setup() {
        // os textos estão localizados no arquivo texts.php deste componente 
        const text = Utils.getTexts('create-agent')
        return { text }
    },

    created() {
        this.iterationFields()
        var stat = 'publish';
    },

    data() {
        return {
            entity: null,
            fields: [],
            /** Issue #32: espelho reativo do tipo — Entity.type não dispara computed ao mudar. */
            agentTypeIdForDocument: null,
        }
    },

    props: {
        editable: {
            type: Boolean,
            default: true
        },
        clickToClose: {
            type: Boolean,
            default: true
        },
        initialType: {
            type: Number,
            default: 1
        },
        lockType: {
            type: Boolean,
            default: false
        },
        teleport: {
            type: null,
            default: false
        },
    },

    computed: {
        areaErrors() {
            return this.entity.__validationErrors['term-area'];
        },
        areaClasses() {
            return this.areaErrors ? 'field error' : 'field';
        },
        /**
         * Issue #32: metadado de documento (cpf/cnpj) exigido conforme o tipo,
         * alinhado a Agent::requiredDocumentMetadata() e ao jsObject do módulo
         * AgentDocuments — exibido no modal de criação quando a flag está ativa.
         */
        requiredDocumentProp() {
            const config = $MAPAS?.config?.agentDocuments;

            if (!config?.requiredDocumentsByType) {
                return null;
            }

            const typeId = this.agentTypeIdForDocument;
            if (!typeId) {
                return null;
            }

            return config.documentMetadataByType?.[typeId] || null;
        },
        modalTitle() {
            if (this.entity?.id) {
                if (this.entity.status == 1) {
                    return __('agenteCriado', 'create-agent');
                } else {
                    return __('criarRascunho', 'create-agent');
                }
            } else {
                return __('criarAgente', 'create-agent');

            }
        },
    },

    methods: {
        /**
         * Entity.type no JS é objeto { id, name } após populate/set — não número.
         */
        agentTypeId() {
            const type = this.entity?.type;
            if (!type) {
                return null;
            }
            if (typeof type === 'object') {
                return Number(type.id) || null;
            }
            return Number(type) || null;
        },
        syncAgentTypeIdForDocument() {
            this.agentTypeIdForDocument = this.agentTypeId();
        },
        onAgentTypeChange() {
            const previousDocument = this.requiredDocumentProp;
            this.syncAgentTypeIdForDocument();
            const nextDocument = this.requiredDocumentProp;
            if (previousDocument && previousDocument !== nextDocument) {
                this.entity[previousDocument] = '';
            }
        },
        iterationFields() {
            let skip = [
                'createTimestamp',
                'id',
                'location',
                'name',
                'shortDescription',
                'status',
                'type',
                '_type',
                'userId',
                'cpf',
                'cnpj',
                'documento',
            ];
            Object.keys($DESCRIPTIONS.agent).forEach((item) => {
                if (!skip.includes(item) && $DESCRIPTIONS.agent[item].required) {
                    this.fields.push(item);
                }
            })
        },
        createEntity() {
            this.entity = Vue.ref(new Entity('agent'));
            this.entity.type = this.initialType;
            // Issue #32: alinha com Agent::setType — sem permissão changeType o
            // servidor SEMPRE cria tipo 2 (Coletivo); sem isto o modal exibe o
            // campo de documento errado (CPF) para quem só cria coletivos.
            const global = useGlobalState();
            if (!global.auth.is('admin')) {
                this.entity.type = 2;
            }
            this.entity.terms = { area: [] }
            this.syncAgentTypeIdForDocument();
        },
        createDraft(modal) {
            this.entity.status = 0;
            this.save(modal);
        },
        createPublic(modal) {
            //lançar dois eventos
            this.entity.status = 1;
            this.save(modal);
        },
        save(modal) {
            const lists = useEntitiesLists(); // obtem o storage de listas de entidades

            if (this.lockType) {
                this.entity.type = this.initialType;
            }

            modal.loading(true);
            this.entity.save().then((response) => {
                this.$emit('create', response);
                modal.loading(false);
                Utils.pushEntityToList(this.entity);
            }).catch((e) => {
                modal.loading(false);

            });
        },

        destroyEntity() {
            // para o conteúdo da modal não sumir antes dela fechar
            setTimeout(() => this.entity = null, 200);
        },
    },
});
