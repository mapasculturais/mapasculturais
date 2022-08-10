
app.component('entity-location', {
    template: $TEMPLATES['entity-location'],
    emits: [],

    setup(props, { slots }) {
        const hasSlot = name => !!slots[name]
        return { hasSlot }
    },

    props: {
        entity: {
            type: Entity,
            required: true
        },
        editable: {
            type: Boolean,
            default: true
        }        
        
    },

    methods: {
        address() {

            let rua         = this.entity.En_Nome_Logradouro == null ? '' : this.entity.En_Nome_Logradouro;
            let numero      = this.entity.En_Num             == null ? '' : this.entity.En_Num;
            let complemento = this.entity.En_Complemento     == null ? '' : this.entity.En_Complemento;
            let bairro      = this.entity.En_Bairro          == null ? '' : this.entity.En_Bairro;
            let cidade      = this.entity.En_Municipio       == null ? '' : this.entity.En_Municipio;
            let estado      = this.entity.En_Estado          == null ? '' : this.entity.En_Estado;
            let cep         = this.entity.En_CEP             == null ? '' : this.entity.En_CEP;

            // rua, num, complemento - bairro - cidade/uf - CEP: 00000000
            var address = '';

            if(rua) {
                address += rua;
            }

            if(numero) {
                if (address) {
                    address += ', ' + numero;
                } else {
                    address += numero;
                }
            }

            if(complemento) {
                if (address) {
                    address += ', ' + complemento;
                } else {
                    address += complemento;
                }
            }

            if(bairro) {
                if (address) {
                    address += ' - ' + bairro;
                } else {
                    address += bairro;
                }
            }

            if (cidade && estado) {
                if (address) {
                    address += ' - ' + cidade + '/' + estado;
                } else {
                    address += cidade + '/' + estado;
                }                
            } else if (cidade) {
                if (address) {
                    address += ' - ' + cidade;
                } else {
                    address += cidade;
                }  
            } else if (estado) {
                if (address) {
                    address += ' - ' + estado;
                } else {
                    address += estado;
                }
            }

            if(cep) {
                if (address) {
                    address += ' - CEP: ' + cep;
                } else {
                    address += 'CEP: ' + cep;
                }
            }
           
            this.entity.endereco = address;
        },

        pesquisacep(valor) {
            //Nova variável "cep" somente com dígitos.
            var cep = valor.replace(/\D/g, '');                
            if (cep != "") {
                var validacep = /^[0-9]{8}$/;   
                if(validacep.test(cep)) {     
                    fetch('//viacep.com.br/ws/'+ cep +'/json/')
                        .then((response) => response.json())
                        .then((data) => {
                            this.entity.En_Nome_Logradouro = data.logradouro;
                            this.entity.En_Bairro = data.bairro;
                            this.entity.En_Municipio = data.localidade;
                            this.entity.En_Estado = data.uf;
                            this.address();
                        });    
                } 
            } 
        }
    }
});
