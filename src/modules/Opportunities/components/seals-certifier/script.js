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
                proponent: this.entityOpportunity.registrationProponentTypes?.length > 0
                    ? this.entityOpportunity.registrationProponentTypes
                    : ['default'],
                category: this.entityOpportunity.registrationCategories?.length > 0
                    ? this.entityOpportunity.registrationCategories
                    : ['default'],
            };
        }
    },

    watch: {
        types: {
            handler(newTypes, oldTypes) {
                const isInitialization = !oldTypes || Object.keys(oldTypes).length === 0;

                this.syncSeals('proponentSeals', newTypes.proponent, this.proponentSeals, isInitialization);
                this.syncSeals('categorySeals', newTypes.category, this.categorySeals, isInitialization);
            },
            deep: true,
            immediate: true
        }
    },

    methods: {
        initializeSeals() {
            let skeletonProponent = {};
            let skeletonCategory = {}; 

            for (let type of this.types.proponent) {
                skeletonProponent[type] = this.entityOpportunity.proponentSeals?.[type] || [];
            }

            for (let type of this.types.category) {
                skeletonCategory[type] = this.entityOpportunity.categorySeals?.[type] || [];
            }

            this.proponentSeals = skeletonProponent;
            this.categorySeals = skeletonCategory;
        },


        syncSeals(sealsGroup, sealsType, sealsObject, isInitialization = false) {
            const newTypes = sealsType;
            const oldTypes = Object.keys(sealsObject);
    
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
    
            if (!isInitialization) {
                this.entityOpportunity[sealsGroup] = { ...sealsObject };
                this.entityOpportunity.save();
            }
        },

        getSealDetails(sealId) {
            return this.sealsInfo.find(seal => seal.id === sealId) || {};
        },

        modifySealList(type, sealId, action, typeSealCertifier) {
            let sealGroup;
            if (typeSealCertifier === 'proponent') {
                sealGroup = 'proponentSeals';
            } else if (typeSealCertifier === 'category') {
                sealGroup = 'categorySeals';
            }
            
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
        
        addSeal(type, seal, typeSealCertifier) {
            this.modifySealList(type, seal._id, 'add', typeSealCertifier);
        }, 
        
        removeSeal(type, sealId, typeSealCertifier) {
            this.modifySealList(type, sealId, 'remove', typeSealCertifier);
        },
        
        getSealQuery(type, typeSealCertifier) {
            let sealGroup;
            if (typeSealCertifier === 'proponent') {
                sealGroup = 'proponentSeals';
            } else {
                sealGroup = 'categorySeals';
            }

            const selectedSeals = this[sealGroup]?.[type] || [];

            const selectedSealIds = selectedSeals.join(',');
        
            return selectedSealIds ? { id: `!IN(${selectedSealIds})` } : {};
        },
    }
});