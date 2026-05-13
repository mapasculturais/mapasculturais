app.component('entity-data', {
    template: $TEMPLATES['entity-data'],

    props: {
        entity: {
            type: Entity,
            required: true,
        },

        prop: {
            type: String,
            required: true,
        },

        label: {
            type: String,
        },

        showRequiredMark: {
            type: Boolean,
            default: false,
        },

        fieldRequired: {
            type: Boolean,
            default: false,
        },
    },

    setup(props, { slots }) {
        const hasSlot = name => !!slots[name];
        // os textos estão localizados no arquivo texts.php deste componente 
        const text = Utils.getTexts('entity-data')
        return { text, hasSlot }
    },

    computed: {
        description() {
            const description = this.entity.$PROPERTIES[this.prop];

            if(!description) {
                console.error(`Propriedade ${this.prop} não encontrada na entidade ${this.entity.__objectType}`);
            }
            return description;
        },

        propertyLabel() {
            return this.label || this.description.label;
        },

        propertyData() {
            return this.normalizeData(this.entity[this.prop]);
        },

        propertyType() {
            return this.description.type;
        },

        shouldShowValueBlock() {
            const type = this.description?.type;
            const raw = this.entity[this.prop];
            if (type === 'checkbox' || type === 'boolean') {
                return raw !== null && raw !== undefined && raw !== '';
            }
            const data = this.normalizeData(raw);
            if (data === null || data === undefined || data === '') {
                return false;
            }
            if (Array.isArray(data) && data.length === 0) {
                return false;
            }
            if (typeof data === 'object' && !Array.isArray(data) && Object.keys(data).length === 0) {
                return false;
            }
            return true;
        },
    },

    methods: {
        getCheckboxValue(data) {
            return data ? this.text('Marcado') : this.text('Desmarcado');
        },

        normalizeData(data) {
            if (typeof data === 'string') {
                const trimmed = data.trim();
                if ((trimmed.startsWith('[') && trimmed.endsWith(']')) || (trimmed.startsWith('{') && trimmed.endsWith('}'))) {
                    try {
                        return JSON.parse(trimmed);
                    } catch (e) {
                        return data;
                    }
                }
            }
            return data;
        },

        normalizeList(data) {
            const normalized = this.normalizeData(data);
            if (Array.isArray(normalized)) {
                return normalized;
            }
            if (typeof normalized === 'string' && normalized.includes(';')) {
                return normalized.split(';').map((item) => item.trim()).filter(Boolean);
            }
            return [normalized].filter(Boolean);
        },

        normalizeText(data) {
            const normalized = this.normalizeData(data);
            if (Array.isArray(normalized)) {
                return normalized.join(', ');
            }
            if (normalized && typeof normalized === 'object') {
                return Object.keys(normalized).filter((key) => normalized[key]).join(', ');
            }
            return normalized;
        },

        selectOptionLabel() {
            const opts = this.description?.options || {};
            const val = this.propertyData;
            return opts[val] ?? opts[String(val)] ?? val;
        },

        getAddress(address) {
            if (typeof address === 'string') {
                address = JSON.parse(address);
            }

            let rua  = address[0].nome ??  '';
            let numero  = address[0].numero ?? '';
            let complemento = address[0].complemento ?? '';
            let bairro = address[0].bairro ?? '';
            let cidade = address[0].cidade ?? '';
            let estado = address[0].estado ?? '';
            let cep   = address[0].cep ?? '';

            var fullAddress = '';

            if(rua) {
                fullAddress += rua;
            }

            if(numero) {
                if (fullAddress) {
                    fullAddress += ', ' + numero;
                } else {
                    fullAddress += numero;
                }
            }

            if(complemento) {
                if (fullAddress) {
                    fullAddress += ', ' + complemento;
                } else {
                    fullAddress += complemento;
                }
            }

            if(bairro) {
                if (fullAddress) {
                    fullAddress += ' - ' + bairro;
                } else {
                    fullAddress += bairro;
                }
            }

            if (cidade && estado) {
                if (fullAddress) {
                    fullAddress += ' - ' + cidade + '/' + estado;
                } else {
                    fullAddress += cidade + '/' + estado;
                }                
            } else if (cidade) {
                if (fullAddress) {
                    fullAddress += ' - ' + cidade;
                } else {
                    fullAddress += cidade;
                }  
            } else if (estado) {
                if (fullAddress) {
                    fullAddress += ' - ' + estado;
                } else {
                    fullAddress += estado;
                }
            }

            if(cep) {
                if (fullAddress) {
                    fullAddress += ' - CEP: ' + cep;
                } else {
                    fullAddress += 'CEP: ' + cep;
                }
            }

            return fullAddress;
        },
    },
});
