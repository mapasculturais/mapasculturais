app.component('entity-address-form-internacional', {
    template: $TEMPLATES['entity-address-form-internacional'],
    emits: [],

    props: {
        entity: {
            type: [Entity, Object],
            required: true
        },
        country: {
            type: String,
            default: 'US' // ISO-3166-1 alpha-2 (ex.: US, BR, AL)
        },
        hierarchy: {
            type: Object,
            default: () => null
        },
        editable: {
            type: Boolean,
            default: false,
        },
    },

    setup(props, { slots }) {
        const hasSlot = name => !!slots[name];
        return { hasSlot };
    },

    data() {
        return {
            // IDs únicos por instância do componente (4 dígitos) – igual ao nacional
            idSuffix: String(Math.floor(1000 + Math.random() * 9000)),
            levelHierarchy: null,   // Estrutura hierárquica já parseada
            selectedLevels: {},     // {1: idx, 2: idx, ...} – sempre numérico
            agentDescription: $MAPAS.EntitiesDescription.agent
        };
    },

    created() {
        this.levelHierarchy = this.parseLevel(this.hierarchy);
    },

    computed: {
        hasPublicLocation() {
            // segue o padrão atual do projeto (controle por schema da entidade)
            return !!(this.entity?.$PROPERTIES?.publicLocation);
        },

        activeLevels() {
            // Ex.: { "1": true, "2": true, "3": false, ... } ou { "level1": true, ... }
            return $MAPAS.config.entityAddressFormInternacional.activeLevels;
        },

        // ---------- Encadeamento de níveis (0..6) ----------
        level0() { // País (raiz)
            return this.levelHierarchy;
        },

        level1() {
            const idx = this.selectedLevels[1];
            if (idx == null || !this.level0?.subLevels?.[idx]) return null;
            const node = this.level0.subLevels[idx];
            this.entity.address_level1 = node.value ?? null;
            return node;
        },

        level2() {
            const idx = this.selectedLevels[2];
            if (!this.level1 || idx == null || !this.level1.subLevels?.[idx]) return null;
            const node = this.level1.subLevels[idx];
            this.entity.address_level2 = node.value ?? null;
            return node;
        },

        level3() {
            const idx = this.selectedLevels[3];
            if (!this.level2 || idx == null || !this.level2.subLevels?.[idx]) return null;
            const node = this.level2.subLevels[idx];
            this.entity.address_level3 = node.value ?? null;
            return node;
        },

        level4() {
            const idx = this.selectedLevels[4];
            if (!this.level3 || idx == null || !this.level3.subLevels?.[idx]) return null;
            const node = this.level3.subLevels[idx];
            this.entity.address_level4 = node.value ?? null;
            return node;
        },

        level5() {
            const idx = this.selectedLevels[5];
            if (!this.level4 || idx == null || !this.level4.subLevels?.[idx]) return null;
            const node = this.level4.subLevels[idx];
            this.entity.address_level5 = node.value ?? null;
            return node;
        },

        level6() {
            const idx = this.selectedLevels[6];
            if (!this.level5 || idx == null || !this.level5.subLevels?.[idx]) return null;
            const node = this.level5.subLevels[idx];
            this.entity.address_level6 = node.value ?? null;
            return node;
        },
    },

    methods: {
        // ---------------- utilitários ----------------
        fid(name) { return `${name}-${this.idSuffix}`; },
        clean(v) { return (v ?? '').toString().trim(); },

        toLevelNum(key) {
            // aceita "1", 1, "level1", "lvl_3" etc.
            const m = String(key).match(/\d+/);
            return m ? Number(m[0]) : NaN;
        },

        getLevel(level) {
            const n = Number(level);
            if (Number.isNaN(n)) return null;
            return this[`level${n}`];
        },

        // Parser da árvore de níveis → { value, label, subLevels[] }
        parseLevel(level, levelKey = null) {
            if (!level) return null;

            const subLevels = [];
            for (let key in level) {
                if (typeof level[key] === 'string') continue; // strings são labels do próprio nó
                let subKey = key;
                if (!isNaN(Number(subKey))) subKey = Number(subKey);
                const parsed = this.parseLevel(level[key], subKey);
                if (parsed) subLevels.push(parsed);
            }

            // Folha com uma lista (label em 0; filhos no array)
            if (Array.isArray(level[0])) {
                return { value: null, label: null, subLevels };
            }

            const value = (typeof levelKey === 'string') ? levelKey : level[0];
            const label = level[0] ?? null;

            return { value: value ?? null, label: label ?? null, subLevels };
        },

        showSubLevelSelect(levelObject, level) {
            if (!levelObject || !Array.isArray(levelObject.subLevels) || levelObject.subLevels.length === 0) {
                return false;
            }
            // único subnível "neutro" (sem value) → pula automaticamente
            if (levelObject.subLevels.length === 1 && !levelObject.subLevels[0].value) {
                this.selectedLevels[level + 1] = 0;
                return false;
            }
            return true;
        },

        clearSubLevels(level) {
            // Limpa escolhas e valores abaixo do nível alterado
            for (let i = level + 1; i <= 6; i++) {
                if (this.selectedLevels[i] != null) delete this.selectedLevels[i];
                if (this.entity[`address_level${i}`] != null) this.entity[`address_level${i}`] = null;
            }
        },

        fieldLabel(level) {
            return this.agentDescription?.[`address_level${level}`]?.label ?? `Nível ${level}`;
        },

        formatParams(params) {
            return "?" + Object.keys(params)
                .map(k => `${k}=${encodeURIComponent(params[k])}`)
                .join("&");
        },

        // ---------------- Geocodificação (Nominatim) ----------------
        async geolocation() {
            const e = this.entity ?? {};

            const line1   = this.clean(e.address_line1);
            const line2   = this.clean(e.address_line2);
            const l6      = this.clean(e.address_level6); // bairro
            const l5      = this.clean(e.address_level5); // distrito
            const l4      = this.clean(e.address_level4); // cidade
            const l3      = this.clean(e.address_level3); // dept/county
            const l2      = this.clean(e.address_level2); // estado/prov
            const cep     = this.clean(e.address_postalCode);
            const country = this.clean(this.country || e.address_level0);

            if (!country || (!l4 && !l2 && !line1)) return;

            const params = {
                format: 'jsonv2',
                countrycodes: country.toLowerCase(),
                limit: '1'
            };

            let structured = false;

            if (line1) { params.street = line1; structured = true; }
            if (l4)    { params.city   = l4;    structured = true; }
            if (l2)    { params.state  = l2;    structured = true; }
            if (l3)    { params.county = l3;    structured = true; }
            if (l5)    { params.suburb = l5;    structured = true; }
            if (l6)    { params.neighbourhood = l6; structured = true; }
            if (cep)   { params.postalcode = cep; structured = true; }

            if (!structured) {
                const full = [line1, line2, l6, l5, l4, l3, l2, cep, country]
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

        // ---------------- Montagem do endereço ----------------
        address() {
            this.entity.address_level0 = this.country || this.entity.address_level0 || null;

            const line1  = this.clean(this.entity.address_line1);
            const line2  = this.clean(this.entity.address_line2);
            const l6     = this.clean(this.entity.address_level6); // bairro
            const l5     = this.clean(this.entity.address_level5); // distrito
            const l3     = this.clean(this.entity.address_level3); // dept/county
            const l4     = this.clean(this.entity.address_level4); // cidade
            const l2     = this.clean(this.entity.address_level2); // estado/prov
            const cep    = this.clean(this.entity.address_postalCode);
            const pais   = this.clean(this.entity.address_level0);

            const parts = [];

            const head = [line1, line2].filter(Boolean).join(', ');
            if (head) parts.push(head);

            const subRegion = [l6, l5, l3].filter(Boolean).join(', ');
            if (subRegion) parts.push(subRegion);

            const cityStateZip = [l4, l2, cep].filter(Boolean).join(', ');
            if (cityStateZip) parts.push(cityStateZip);

            if (pais) parts.push(pais);

            this.entity.address = parts.join(' - ');
            this.geolocation();
        },

        // Layout responsivo dos "inputs livres"
        getColumnClass(level, levelKeys) {
            const nums = (Array.isArray(levelKeys) ? levelKeys : [])
                .map(k => this.toLevelNum(k))
                .filter(n => !isNaN(n))
                .sort((a,b) => a - b);

            const idx = nums.indexOf(level);
            const total = nums.length;

            if (total % 2 === 0 && idx === total) return 'col-12';
            return 'col-6';
        },
    },
});
