
app.component('entity-location', {
    template: $TEMPLATES['entity-location'],
    emits: [],

    setup(props, { slots }) {
        const hasSlot = name => !!slots[name]
        return { hasSlot }
    },

    updated() {
        this.address();
        console.log('teste');
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
            var address = '';        
            address += this.entity.En_Nome_Logradouro;
            address += address != '' && this.entity.En_Complemento  ? ', '+this.entity.En_Complemento : this.entity.En_Complemento;
            address += address != '' && this.entity.En_Bairro       ? ', '+this.entity.En_Bairro : this.entity.En_Bairro;
            address += address != '' && this.entity.En_Municipio    ? ', '+this.entity.En_Municipio : this.entity.En_Municipio;
            address += address != '' && this.entity.En_Num          ? ', '+this.entity.En_Num : this.entity.En_Num;
            address += address != '' && this.entity.En_Estado       ? ', '+this.entity.En_Estado : this.entity.En_Estado;
            address += address != '' && this.entity.En_CEP          ? ', '+this.entity.En_CEP : this.entity.En_CEP;
           
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
