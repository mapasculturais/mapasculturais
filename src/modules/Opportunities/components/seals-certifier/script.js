app.component('seals-certifier', {
    template: $TEMPLATES['seals-certifier'],

    props: {
        entity: {
            type: Entity,
            required: true
        },

        editable: {
            type: Boolean,
            default: false
        },

        showName: {
            type: Boolean,
            default: false
        },

        title: {
            type: String,
            default: __('certificador', 'seals-certifier'),
        },
    },

    setup() {
        // os textos estão localizados no arquivo texts.php deste componente 
        const text = Utils.getTexts('seals-certifier')

        return { text }
    },

    data() {
        return {
            proponentSeals: {},
            categorySeals: {},
            sealsInfo: $MAPAS.config.sealsCertifier.seals,
            entityOpportunity: (this.entity.__objectType === 'opportunity') ? this.entity : (this.entity.opportunity || {}),
        }
    },

    mounted() {
        this.initializeSeals();
    },

    computed: {
        types() {
            return {
                proponent: this.entityOpportunity.registrationProponentTypes?.length
                    ? this.entityOpportunity.registrationProponentTypes
                    : ['default'],
                category: this.entityOpportunity.registrationCategories?.length
                    ? this.entityOpportunity.registrationCategories
                    : ['default'],
            };
        }
    },

    watch: {
        'entityOpportunity.registrationProponentTypes': function(newProponentTypes) {
            this.syncSeals('proponentSeals', newProponentTypes, this.proponentSeals);
        },

        'entityOpportunity.registrationCategories': function(newCategories) {
            this.syncSeals('categorySeals', newCategories, this.categorySeals);
        }
    },

    methods: {
        initializeSeals() {
            Object.keys(this.types).forEach(group => {
                this.types[group].forEach(type => {
                    if (!this.entityOpportunity[`${group}Seals`]) {
                        this.entityOpportunity[`${group}Seals`] = {};
                    } else if (!this.entityOpportunity[`${group}Seals`]?.[type]) {
                        this.entityOpportunity[`${group}Seals`][type] = [];
                    }

                    const groupSeals = this.entityOpportunity[`${group}Seals`]?.[type] || [];
                    if (group === 'proponent') {
                        this.proponentSeals[type] = groupSeals;
                    } else if (group === 'category') {
                        this.categorySeals[type] = groupSeals;
                    }
                });
            });
        },

        // não está sincronizando os selos selecionados de forma reativa
        syncSeals(sealsGroup, sealsType, sealsObject) {
            const newTypes = sealsType;
            const oldTypes = Object.keys(sealsObject);
            console.log('newTypes', newTypes);
    
            for (let type of newTypes) {
                if (!oldTypes.includes(type)) {
                    sealsObject[type] = [];
                }
            }
            
            for (let type of oldTypes) {
                if (!newTypes.includes(type)) {
                    delete sealsObject[type]; 
                    if (sealsGroup === 'categorySeals') {
                        delete this.entityOpportunity.categorySeals[type];  
                    }

                    if (sealsGroup === 'proponentSeals') {
                        delete this.entityOpportunity.proponentSeals[type];  
                    }
                }
            }
    
            this.entityOpportunity[sealsGroup] = { ...sealsObject };
        },

        getSealDetails(sealId) {
            return this.sealsInfo.find(seal => seal.id === sealId) || {};
        },

        modifySealList(type, sealId, action) {
            const sealGroup = this.types.proponent.includes(type) ? 'proponentSeals' : 'categorySeals';

            if (!this.entityOpportunity[sealGroup]) {
                this.entityOpportunity[sealGroup] = {};
            }
        
            if (!this.entityOpportunity[sealGroup][type]) {
                this.entityOpportunity[sealGroup][type] = [];
            }
        
            const seals = this.entityOpportunity[sealGroup][type];
            let index = seals.indexOf(sealId);
        
            if (action === 'add' && index === -1) seals.push(sealId);
            if (action === 'remove' && index !== -1) seals.splice(index, 1);

            this.entityOpportunity.save();
        },
        
        addSeal(type, seal) {
            this.modifySealList(type, seal._id, 'add');
        },
        
        removeSeal(type, sealId) {
            this.modifySealList(type, sealId, 'remove');
        },
        
        getSealQuery(type) {
            if (type === 'default') return [];
            const sealGroup = this.types.proponent.includes(type) ? 'proponentSeals' : 'categorySeals';
        
            const selectedSeals = this[sealGroup]?.[type] || [];
            const selectedSealIds = selectedSeals.join(',');
        
            return selectedSealIds ? { id: `!IN(${selectedSealIds})` } : {};
        },
    }
});
