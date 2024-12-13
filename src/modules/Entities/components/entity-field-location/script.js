
app.component('entity-field-location', {
    template: $TEMPLATES['entity-field-location'],

    computed: {
        cities(){
            return this.addressData.En_Estado ? this.statesAndCities[this.addressData.En_Estado].cities : null;
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

    data() {
        return {
            addressData: {}
        }
    },

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
            default: () => {}
        }
    },

    methods: {
        populateEntity() {
            this.initializeAddressFields('entity');

            Object.keys(this.addressData).forEach(addressField => {
                this.entity[this.fieldName][addressField] = this.addressData[addressField];
            })
        },

        address() {
            this.initializeAddressFields('entity');
            this.entity.En_Pais = this.statesAndCitiesCountryCode == 'BR' ? this.statesAndCitiesCountryCode : this.entity.En_Pais;
            let rua         = this.entity[this.fieldName]?.En_Nome_Logradouro == null ? '' : this.entity[this.fieldName].En_Nome_Logradouro;
            let numero      = this.entity[this.fieldName]?.En_Num             == null ? '' : this.entity[this.fieldName].En_Num;
            let complemento = this.entity[this.fieldName]?.En_Complemento     == null ? '' : this.entity[this.fieldName].En_Complemento;
            let bairro      = this.entity[this.fieldName]?.En_Bairro          == null ? '' : this.entity[this.fieldName].En_Bairro;
            let cidade      = this.entity[this.fieldName]?.En_Municipio       == null ? '' : this.entity[this.fieldName].En_Municipio;
            let estado      = this.entity[this.fieldName]?.En_Estado          == null ? '' : this.entity[this.fieldName].En_Estado;
            let cep         = this.entity[this.fieldName]?.En_CEP             == null ? '' : this.entity[this.fieldName].En_CEP;

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
           
            this.entity[this.fieldName].endereco = address;

            if(this.configs?.setLatLon) {
                this.geolocation();
            }
        },

        pesquisacep(valor, executeAddress) {
            //Nova variável "cep" somente com dígitos.
            var cep = valor.replace(/\D/g, '');                
            if (cep != "") {
                var validacep = /^[0-9]{8}$/;   
                if(validacep.test(cep)) {     
                    fetch('//viacep.com.br/ws/'+ cep +'/json/')
                        .then((response) => response.json())
                        .then((data) => {
                            this.addressData.En_Nome_Logradouro = data.logradouro;
                            this.addressData.En_Bairro = data.bairro;
                            this.addressData.En_Municipio = data.localidade;
                            this.addressData.En_Estado = data.uf;

                            if(executeAddress) {
                                this.address();
                            }
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
            let rua         = this.entity[this.fieldName].En_Nome_Logradouro || '';
            let numero      = this.entity[this.fieldName].En_Num             || '';
            let bairro      = this.entity[this.fieldName].En_Bairro          || '';
            let cidade      = this.entity[this.fieldName].En_Municipio       || '';
            let estado      = this.entity[this.fieldName].En_Estado          || '';
            let cep         = this.entity[this.fieldName].En_CEP             || '';
            
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
                if (!structured && addressElements.fullAddress) {
                    params.q = addressElements.fullAddress;
                }

                let url = 'https://nominatim.openstreetmap.org/search' + this.formatParams(params);
                fetch(url)
                    .then( response => response.json() )
                    .then( r => {
                        // Consideramos o primeiro resultado
                        if (r[0] && r[0].lat && r[0].lon) {
                            this.entity[this.fieldName].location = {lat: r[0].lat, lng: r[0].lon};
                        }
                    } );
            }            
        },
        initializeAddressFields(object) {
            if (!this[object][this.fieldName]) {
                this[object][this.fieldName] = {};
            }

            const requiredFields = [
                'En_CEP',
                'En_Estado',
                'En_Nome_Logradouro',
                'En_Num',
                'En_Bairro',
                'En_Complemento',
                'En_Pais',
                'En_Municipio',
                'publicLocation'
            ];

            requiredFields.forEach(field => {
                if (this[object][this.fieldName][field] === undefined || this[object][this.fieldName][field] === null) {
                    this[object][this.fieldName][field] = '';
                    if(object === 'addressData' && this.entity?.[this.fieldName]?.[field]) {
                        this[object][field] = this.entity[this.fieldName][field];
                    } 
                }
            });
        },

        citiesList(){
            this.cities = this.statesAndCities[this.addressData.En_Estado].cities;
        },

        save() {
            if(this.addressData.En_Num) {
                this.populateEntity();
                this.entity.save();
            }
        }
    },

    created() {
        this.initializeAddressFields('addressData');
    }
});
