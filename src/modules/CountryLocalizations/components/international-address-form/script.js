
app.component('international-address-form', {
    template: $TEMPLATES['international-address-form'],
    emits: [],

    props: {
        entity: {
            type: Entity,
            required: true
        },
        country: {
            type: String,
            default: 'US'
        },
        hierarchy: {
            type: Object,
            default: () => null
        },
        classes: {
            type: [String, Array, Object],
            required: false
        },
    },

    setup(props, { slots }) {
        const hasSlot = name => !!slots[name]
        return { hasSlot }
    },
    data() {
        const levelHierarchy = {};

        return {
            levelHierarchy,
            selectedLevels: {},
            agentDescription: $MAPAS.EntitiesDescription.agent
        };
    },

    created() {
        this.levelHierarchy = this.parseLevel(this.hierarchy);
    },

    computed: {
        activeLevels() {
            return $MAPAS.config.internationalAddressForm.activeLevels;
        },

        level0() { // País
            return this.levelHierarchy;
        },

        level1() { // Região
            if(this.selectedLevels['1'] == undefined) {
                return null;
            }

            this.entity.address_level1 = this.levelHierarchy.subLevels[this.selectedLevels['1']].value;
            return this.levelHierarchy.subLevels[this.selectedLevels['1']]
        },

        level2() { // Estado/Província
            if(this.selectedLevels['2'] == undefined) {
                return null;
            }

            if(!this.level1) {
                return null;
            }

            this.entity.address_level2 = this.level1.subLevels[this.selectedLevels['2']].value;
            return this.level1.subLevels[this.selectedLevels['2']];
        },

        level3() { // Departamento
            if(this.selectedLevels['3'] == undefined) {
                return null;
            }

            if(!this.level2) {
                return null;
            }

            this.entity.address_level3 = this.level2.subLevels[this.selectedLevels['3']].value;
            return this.level2.subLevels[this.selectedLevels['3']];

        },

        level4() { // Cidade/Município/Comune
            if(this.selectedLevels['4'] == undefined) {
                return null;
            }

            if(!this.level3) {
                return null;
            }

            this.entity.address_level4 = this.level3.subLevels[this.selectedLevels['4']].value;
            return this.level3.subLevels[this.selectedLevels['4']];
        },

        level5() { // Subprefeitura/Distrito
            if(this.selectedLevels['5'] == undefined) {
                return null;
            }

            if(!this.level4) {
                return null;
            }

            this.entity.address_level5 = this.level4.subLevels[this.selectedLevels['5']].value;
            return this.level4.subLevels[this.selectedLevels['5']];
        },

        level6() { // Bairro
            if(this.selectedLevels['6'] == undefined) {
                return null;
            }

            if(!this.level5) {
                return null;
            }

            this.entity.address_level6 = this.level5.subLevels[this.selectedLevels['6']].value;
            return this.level5.subLevels[this.selectedLevels['6']];
        },
    },

    methods: {
        getLevel(level) {
            return this[`level${level}`];
        },

        parseLevel(level, levelKey) {
            if(!level) {
                return null;
            }

            const subLevels = [];

            for (let key in level) {
                let subLevelKey = key;

                if(parseInt(subLevelKey) !== NaN) {
                    subLevelKey = parseInt(subLevelKey);
                }

                // Se é uma string, o item é o label
                if(typeof level[key] == 'string') {
                    continue;
                }

                const parsedSublevel = this.parseLevel(level[key], subLevelKey);
                subLevels.push(parsedSublevel);
            }
            
            // 1 - Level está vazio (chave 0 é um array)
            if(level[0] instanceof Array) {
                return {value: null, label: null, subLevels};
            }

            // 2 - Se o valor do levelKey é uma string, ele é o valor do level.
            // 3 - Se o valor do levelKey é um numérico, o valor do level é a chave 0
            const value = (typeof levelKey) == 'string' ? levelKey : level[0];

            // 4 - Label é a chave 0
            const label = level[0];

            return {value, label, subLevels};
        },

        showSubLevelSelect(levelObject, level) {
            if(!levelObject) {
                return false;
            }

            if(levelObject.subLevels.length == 0) {
                return false;
            }

            if(levelObject.subLevels.length == 1 && !levelObject.subLevels[0].value) {
                this.selectedLevels[level+1] = 0;
                return false;
            }

            return true;
        },

        clearSubLevels(level) {
            for(let i = level+1; i <= 6; i++) {
                delete this.selectedLevels[i];
            }
        },

        fieldLabel(level) {
            return this.agentDescription[`address_level${level}`].label;
        },

        formatParams( params ){
            return "?" + Object.keys(params).map(function(key){
                            return key+"="+encodeURIComponent(params[key])
                        }).join("&");
        },

        geolocation() {
            let rua         = this.entity.address_line1 == null ? this.entity.address_line2 : this.entity.address_line1
            let bairro      = this.entity.address_level6          == null ? '' : this.entity.address_level6;
            let cidade      = this.entity.address_level4       == null ? '' : this.entity.address_level4;
            let estado      = this.entity.address_level2          == null ? '' : this.entity.address_level2;
            let cep         = this.entity.address_postalCode             == null ? '' : this.entity.address_postalCode;
            let departamento = this.entity.address_level3             == null ? '' : this.entity.address_level3;

            if (estado && cidade) {
                var address = bairro ?
                    rua + ", " + bairro + ", " + cidade + ", " + estado :
                    rua + ", " + cidade + ", " + estado;

                var addressElements = {
                    fullAddress: address,
                    streetName: rua,
                    city: cidade,
                    state: estado,
                };

                if (bairro) {
                    addressElements["neighborhood"] = bairro;
                }

                if (cep) {
                    addressElements["postalCode"] = cep;
                }

                if (departamento) {
                    addressElements["county"] = departamento;
                }
                var params = {
                    format: "json",
                    countrycodes: this.country
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
                if (addressElements.county) {
                    params.county = addressElements.county;
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

        address() {
            this.entity.address_level0 = this.country;

            const line1         = this.entity.address_line1 ?? '';
            const line2         = this.entity.address_line2 ?? '';
            const bairro        = this.entity.address_level6 ?? '';
            const departamento  = this.entity.address_level3 ?? '';
            const cidade        = this.entity.address_level4 ?? ''; 
            const estado        = this.entity.address_level2 ?? '';
            const cep           = this.entity.address_postalCode ?? '';
            const pais          = this.entity.address_level0 ?? '';

            const addressParts = [];

            if (line1) addressParts.push(line1);
            if (line2) addressParts.push(line2);

            const subRegion = [bairro, departamento].filter(Boolean).join(', ');
            if (subRegion) addressParts.push(subRegion);

            const cityLine = [cidade, estado, cep].filter(Boolean).join(', ');
            if (cityLine) addressParts.push(cityLine);

            if (pais) addressParts.push(pais);

            this.entity.address = addressParts.join(', ');
            this.geolocation();
        }

    },
});
