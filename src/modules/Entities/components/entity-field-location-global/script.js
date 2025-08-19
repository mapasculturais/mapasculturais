app.component('entity-field-location-global', {
    template: $TEMPLATES['entity-field-location-global'],
    emits: [],

    props: {
        entity: {
            type: Entity,
            required: true
        },
        fieldName: {
            type: String,
            required: true
        },
        configs: {
            type: Object,
            default: () => ({})
        },
        classes: {
            type: [String, Array, Object],
            required: false
        },
    },

    data() {
        return {
            country: null,
            levelHierarchy: null,
            processing: false,
            _reqToken: 0,
            feedback: '',
        };
    },

    computed: {
        // Alvo direto: this.entity[this.fieldName]
        model() {
            return this.entity?.[this.fieldName] || null;
        },

        // Lista de países vinda do ambiente Mapas
        countries() {
            return $MAPAS.countries.map(item => ({
                label: item.nome_pais_int,
                value: item.sigla,
            }));
        },

        countryFieldEnabled() {
            return $MAPAS.config.countryLocalization.countryFieldEnabled;
        },

        targetEntity() {
            const e = this.entity;
            const fieldType = this.configs?.fieldType;

            const pick = {
                'agent-owner-field': e => e?.owner,
                'agent-collective-field': e => e?.relatedAgents?.coletivo?.[0],
                'space-field': e => e?.relatedSpaces?.[0],
            }[fieldType];

            return pick ? pick(e) : null;
        },

        hasLinkedEntity() {
            return !!this.targetEntity;
        },

        hasPublicLocation() {
            return this.configs?.fieldType !== 'space-field';
        },
    },

    watch: {
        // Mantém sincronizado se algum subform mudar o level0
        'model.address_level0'(val) {
            if (val && val !== this.country) {
                this.country = val;
                this.loadLevelHierarchy();
            }
        },

        // Se o fieldName mudar (raro), re-inicializa
        fieldName() {
            this.initModel();
        },

        // Define o feedback conforme a ausência/presença do vínculo
        targetEntity: {
            immediate: true,
            handler(val) {
                if (val) {
                    this.feedback = '';
                    return;
                }
                const ft = this.configs?.fieldType;
                const msgMap = {
                    'agent-owner-field': 'Vincule um agente primeiro',
                    'agent-collective-field': 'Vincule um agente coletivo primeiro',
                    'space-field': 'Vincule um espaço primeiro',
                };
                this.feedback = msgMap[ft] || 'Vincule a entidade requerida primeiro';
            }
        },
    },

    methods: {
        clean(v) { return (v ?? '').toString().trim(); },

        // Garante que entity[fieldName] exista com a estrutura mínima
        ensureModel() {
            if (!this.entity[this.fieldName]) {
                this.entity[this.fieldName] = {
                    address_postalCode: null,
                    address_level0: null, // país
                    address_level1: null,
                    address_level2: null, // UF (BR)
                    address_level3: null,
                    address_level4: null, // Município (BR)
                    address_level5: null,
                    address_level6: null, // Bairro (BR)
                    address_line1: null, // Rua, número (combinado em subform)
                    address_line2: null, // Complemento
                    location: null, // { lat, lng }
                    publicLocation: false
                };
            }
        },

        initModel() {
            this.ensureModel();
            // país atual → do model ou default da config
            const def = $MAPAS.config.countryLocalization.countryDefaultCode;
            this.country = this.model.address_level0 || def;
            this.model.address_level0 = this.country;
            this.loadLevelHierarchy();
        },

        // Disparado pelo <select> de país no template
        async changeCountry(selected) {
            const value = (selected && typeof selected === 'object' && 'value' in selected)
                ? selected.value
                : selected;

            this.country = value;
            this.model.address_level0 = value;

            // Ao trocar país, limpe os níveis/linhas específicos (o subform repopula)
            this.clearAddressSpecificFields();
            this.initModel();
            await this.loadLevelHierarchy();
        },

        clearAddressSpecificFields() {
            Object.assign(this.model, {
                address_postalCode: null,
                address_level1: null,
                address_level2: null,
                address_level3: null,
                address_level4: null,
                address_level5: null,
                address_level6: null,
                address_line1: null,
                address_line2: null,
                location: null
            });
        },

        async loadLevelHierarchy() {
            if (!this.country) {
                this.levelHierarchy = null;
                return;
            }

            this.processing = true;
            const myToken = ++this._reqToken;

            try {
                const api = new API('country-localization');
                const data = { country: this.country };
                const url = api.createApiUrl('findLevelHierarchy', data);

                const res = await api.GET(url, data);
                const json = await res.json();

                if (myToken !== this._reqToken) return;
                this.levelHierarchy = json?.error ? null : json;
            } catch (e) {
                console.error(e);
                if (myToken !== this._reqToken) return;
                this.levelHierarchy = null;
            } finally {
                if (myToken === this._reqToken) this.processing = false;
            }
        },
    },

    mounted() {
        this.initModel();
    },
});