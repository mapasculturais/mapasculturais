
app.component('entity-location', {
    template: $TEMPLATES['entity-location'],
    emits: [],

    setup(props, { slots }) {
        const hasSlot = name => !!slots[name]
        return { hasSlot }
    },
    data(){
        let cities = {};
        return {cities};
    },

    computed: {
        hasPublicLocation() {
            return !!this.entity.$PROPERTIES.publicLocation;
        },
        statesAndCities(){
            return $MAPAS.config.statesAndCities;
        },
        statesAndCitiesEnable(){
            return $MAPAS.config.statesAndCitiesEnable;
        },
        statesAndCitiesCountryCode() {
            return $MAPAS.config.statesAndCitiesCountryCode;
        },
        states(){
            let states = [];
            Object.keys(this.statesAndCities).forEach((item) => {
                let data = {
                    value: item,
                    label: this.statesAndCities[item].label
                }
                states.push(data);
            });

            if(this.entity.En_Estado){
                this.citiesList();
            }

            return states;
        },
    },

    props: {
        entity: {
            type: Entity,
            required: true
        },
        editable: {
            type: Boolean,
            default: false,
        },
        hideLabel: {
            type: Boolean,
            default: false,
        },
        classes: {
            type: [String, Array, Object],
            required: false
        },
    },

    watch: {
        'entity.En_Pais'(_new, _old){
            if(_new != _old) {
                this.entity.En_Nome_Logradouro = "";
                this.entity.En_Num             = "";
                this.entity.En_Complemento     = "";
                this.entity.En_Bairro          = "";
                this.entity.En_Municipio       = "";
                this.entity.En_Estado          = "";
                this.entity.En_CEP             = "";
            }
        }
    },

    methods: {
        propId(prop) {
            let uid = Math.random().toString(36).slice(2);
            return`${this.entity.__objectId}--${prop}--${uid}`;
        },
        verifiedAdress() {
            if(this.entity.currentUserPermissions['@control']){
                return true;
            };
            const fields = ['En_Nome_Logradouro', 'En_Num', 'En_Bairro', 'En_Municipio', 'En_Estado', 'En_CEP'];
            let result = !this.hasPublicLocation || this.entity.publicLocation;
            fields.forEach((element)=> {
                if(this.entity[element]==null) {
                    result = false;
                    return;
                }
            });
               
            return result;
        },
        address() {
            this.entity.En_Pais == null ? '' : this.entity.En_Pais;
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
            this.geolocation();
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
        },

        formatParams( params ){
            return "?" + Object.keys(params).map(function(key){
                            return key+"="+encodeURIComponent(params[key])
                        }).join("&");
        },

        geolocation() {
            let rua         = this.entity.En_Nome_Logradouro == null ? '' : this.entity.En_Nome_Logradouro;
            let numero      = this.entity.En_Num             == null ? '' : this.entity.En_Num;
            let bairro      = this.entity.En_Bairro          == null ? '' : this.entity.En_Bairro;
            let cidade      = this.entity.En_Municipio       == null ? '' : this.entity.En_Municipio;
            let estado      = this.entity.En_Estado          == null ? '' : this.entity.En_Estado;
            let cep         = this.entity.En_CEP             == null ? '' : this.entity.En_CEP;

            if (estado && cidade) {
                var address = bairro ?
                    rua + " " + numero + ", " + bairro + ", " + cidade + ", " + estado :
                    rua + " " + numero + ", " + cidade + ", " + estado;

                var addressElements = {
                    fullAddress: address,
                    streetName: rua,
                    city: cidade,
                    state: estado,
                };

                if (numero)
                    addressElements["number"] = numero;

                if (bairro)
                    addressElements["neighborhood"] = bairro;

                if (cep)
                    addressElements["postalCode"] = cep;

                var params = {
                    format: "json",
                    countrycodes: "br"
                };
                var structured = false;

                if (addressElements.streetName) {
                    params.street = (addressElements.number ? addressElements.number + " " : "") + addressElements.streetName;
                    structured = true;
                }
                if (addressElements.city) {
                    params.city = addressElements.city;
                    structured = true;
                }
                if (addressElements.state) {
                    params.state = addressElements.state;
                    structured = true;
                }
                if (addressElements.country) {
                    params.country = addressElements.country;
                    structured = true;
                }
                // Parece que o nominatim não se dá bem com nosso CEP
                // if (addressElements.postalCode) {
                //     params.postalcode = addressElements.postalCode;
                //     structured = true;
                // }
                if (!structured && addressElements.fullAddress) {
                    params.q = addressElements.fullAddress;
                }

                let url = 'https://nominatim.openstreetmap.org/search' + this.formatParams(params);
                fetch(url)
                    .then( response => response.json() )
                    .then( r => {
                        // Consideramos o primeiro resultado
                        if (r[0] && r[0].lat && r[0].lon) {
                            this.entity.location = {lat: r[0].lat, lng: r[0].lon};
                        }
                    } );
            }            
        },
        citiesList(){
            this.cities = this.statesAndCities[this.entity.En_Estado].cities;
        },
        isRequired(field){
            return $DESCRIPTIONS[this.entity.__objectType][field].required;
        }
    }
});
