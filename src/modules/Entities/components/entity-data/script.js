app.component('entity-data', {
    template: $TEMPLATES['entity-data'],

    props: {
        entity: {
            type: Entity,
            required: true
        },

        prop: {
            type: String,
            required: true,
        },

        label: {
            type: String,
        }
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
            return this.entity[this.prop];
        },

        propertyType() {
            return this.description.type;
        },
    },

    methods: {
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

            console.log('getAddress', fullAddress);
            return fullAddress;
        },
    }
    
});
