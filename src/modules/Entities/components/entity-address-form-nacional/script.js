app.component('entity-address-form-nacional', {
    template: $TEMPLATES['entity-address-form-nacional'],
    emits: [],

    props: {
        entity: {
            type: [Entity, Object],
            required: true
        },
        hierarchy: {
            type: Object,
            default: () => null
        },
        hasPublicLocation: Boolean,
        hasErrors: {
            type: Boolean,
            default: false,
        },
        missingKeys: {
            type: Array,
            default: () => [],
        },
        requiredFields: {
            type: Object,
            default: () => ({})
        },
    },

    setup(props, { slots }) {
        const hasSlot = name => !!slots[name];
        return { hasSlot };
    },

    data() {
        return {
            // IDs únicos por instância do componente (4 dígitos)
            idSuffix: String(Math.floor(1000 + Math.random() * 9000)),
            cities: [],
            addressStreet: '',
            addressNumber: '',
        };
    },

    mounted() {
        const line1 = this.clean(this.entity.address_line1);
        if (line1) {
            const { street, number } = this.splitLine1(line1);
            this.addressStreet = street;
            this.addressNumber = number;
        }
    },

    computed: {
        statesAndCities() { return this.hierarchy || []; },
        states() {
            let states = [];
            if (this.statesAndCities.length > 0) {
                const statesArray = this.statesAndCities[1];
                Object.keys(statesArray).forEach((uf) => {
                    const stateData = statesArray[uf];
                    states.push({ value: uf, label: stateData[0] });
                });
            }
            if (this.entity.address_level2) this.citiesList();
            return states.sort((a, b) => a.label.localeCompare(b.label));
        }
    },

    methods: {
        // Gera o ID no formato "<nome>-<idSuffix>"
        fid(name) { return `${name}-${this.idSuffix}`; },

        clean(v) { return (v ?? '').toString().trim(); },

        // Verifica se o subcampo é obrigatório
        isRequired(addressKey) {
            return !!this.requiredFields?.[addressKey];
        },

        // Verifica se o subcampo obrigatório está faltando (após validação do backend)
        hasError(addressKey) {
            console.log(this.hasErrors, this.missingKeys, addressKey);
            return this.hasErrors && this.missingKeys.includes(addressKey);
        },

        splitLine1(line1) {
            const s = this.clean(line1);
            if (s.includes(',')) {
                const [street, ...rest] = s.split(',');
                return { street: this.clean(street), number: this.clean(rest.join(',')) };
            }
            let m = s.match(/^(\S+)\s+(.+)$/);
            if (m && /^\d/.test(m[1])) return { street: this.clean(m[2]), number: this.clean(m[1]) };
            m = s.match(/^(.+)\s+(\S+)$/);
            if (m && /\d/.test(m[2])) return { street: this.clean(m[1]), number: this.clean(m[2]) };
            return { street: s, number: '' };
        },

        filledAdress() {
            const requiredEntity = ['address_level6', 'address_level4', 'address_level2', 'address_postalCode'];
            if (!this.clean(this.addressStreet) || !this.clean(this.addressNumber)) return false;
            for (const field of requiredEntity) if (!this.clean(this.entity[field])) return false;
            return !this.hasPublicLocation || this.entity.publicLocation;
        },

        address() {
            if (this.statesAndCitiesCountryCode === 'BR') this.entity.address_level0 = 'BR';

            const street = this.clean(this.addressStreet);
            const number = this.clean(this.addressNumber);
            const line2 = this.clean(this.entity.address_line2);
            const bairro = this.clean(this.entity.address_level6);
            const cidade = this.clean(this.entity.address_level4);
            const estado = this.clean(this.entity.address_level2);
            const cep = this.clean(this.entity.address_postalCode);

            this.entity.address_line1 = [street, number].filter(Boolean).join(', ');

            const head = [this.entity.address_line1, line2].filter(Boolean).join(', ');
            const cityState = (cidade && estado) ? `${cidade}/${estado}` : (cidade || estado);
            const parts = [head, bairro, cityState, cep ? `CEP: ${cep}` : ''].filter(Boolean);

            this.entity.endereco = parts.join(' - ');
            this.geolocation();
        },

        async pesquisacep(valor) {
            const cep = String(valor ?? '').replace(/\D/g, '');
            if (!/^\d{8}$/.test(cep)) return;

            try {
                const res = await fetch(`https://viacep.com.br/ws/${cep}/json/`, { headers: { Accept: 'application/json' } });
                if (!res.ok) return;

                const data = await res.json();
                if (data?.erro) return;

                this.addressStreet = this.clean(data.logradouro);

                const setIf = (key, val) => { if (val) this.entity[key] = val; };
                setIf('address_level6', this.clean(data.bairro));
                setIf('address_level4', this.clean(data.localidade));
                setIf('address_level2', data.uf?.toUpperCase());
                setIf('address_postalCode', data.cep ?? cep);

                this.address();
            } catch (err) {
                console.error('Erro ao consultar ViaCEP:', err);
            }
        },

        formatParams(params) {
            return "?" + Object.keys(params).map(key => key + "=" + encodeURIComponent(params[key])).join("&");
        },

        async geolocation() {
            const e = this.entity ?? {};
            const street = this.clean(this.addressStreet);
            const number = this.clean(this.addressNumber);
            const bairro = this.clean(e.address_level6);
            const cidade = this.clean(e.address_level4);
            const estado = this.clean(e.address_level2);
            const cep = this.clean(e.address_postalCode);

            if (!estado || !cidade) return;

            const params = { format: 'jsonv2', countrycodes: 'br', limit: '1' };
            let structured = false;

            if (street || number) {
                const streetForSearch = (number ? `${number} ${street}` : street)
                    .replace(',', ' ').replace(/\s+/g, ' ').trim();
                if (streetForSearch) { params.street = streetForSearch; structured = true; }
            }
            if (cidade) { params.city = cidade; structured = true; }
            if (estado) { params.state = estado; structured = true; }
            if (bairro) { params.neighbourhood = bairro; structured = true; }
            if (cep) { params.postalcode = cep; structured = true; }

            if (!structured) {
                const full = [[street, number].filter(Boolean).join(', '), bairro, cidade, estado, cep]
                    .filter(Boolean).join(', ');
                if (!full) return;
                params.q = full;
            }

            const url = 'https://nominatim.openstreetmap.org/search' + this.formatParams(params);

            try {
                const res = await fetch(url, { headers: { Accept: 'application/json' } });
                if (!res.ok) throw new Error(`HTTP ${res.status}`);
                const data = await res.json();
                const first = data?.[0];
                if (first?.lat && first?.lon) {
                    this.entity.location = { lat: Number(first.lat), lng: Number(first.lon) };
                }
            } catch (err) {
                console.error('Falha ao geocodificar:', err);
            }
        },

        citiesList() {
            if (!Array.isArray(this.statesAndCities) || this.statesAndCities.length < 2) { this.cities = []; return; }
            const uf = (this.entity?.address_level2 ?? '').toString().trim().toUpperCase();
            const statesMap = this.statesAndCities?.[1];
            const rawCities = statesMap?.[uf]?.[1];
            if (!Array.isArray(rawCities)) { this.cities = []; return; }
            const collator = new Intl.Collator('pt-BR', { sensitivity: 'base' });
            this.cities = rawCities
                .map(c => (Array.isArray(c) ? c[0] : c))
                .filter(Boolean)
                .map(String)
                .sort((a, b) => collator.compare(a, b));
        }
    }
});