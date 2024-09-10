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
            sealsInfo: $MAPAS.config.sealsCertifier.seals,
            entityOpportunity: (this.entity.__objectType === 'opportunity') ? this.entity : (this.entity.opportunity || {}),
        }
    },


    mounted() {
        this.initializeProponentSeals();
    },

    computed: {
        proponentTypes() {
            return this.entityOpportunity.registrationProponentTypes.length 
                ? this.entityOpportunity.registrationProponentTypes 
                : ['default']; 
        },

        proponentSeals() {
            let proponentSeals = {};

            for (let proponentType of this.proponentTypes) {
                proponentSeals[proponentType] = this.entityOpportunity.proponentSeals[proponentType] || [];
            }

            return proponentSeals;
        }
    },

    methods: {
        initializeProponentSeals() {
            if (!this.entityOpportunity.proponentSeals || Object.keys(this.entityOpportunity.proponentSeals).length === 0) {
                this.entityOpportunity.proponentSeals = this.skeleton();
            } 
        },

        skeleton() {
            const seals = {};

            for (let proponentType of this.proponentTypes) {
                seals[proponentType] = [];
            }
            
            return seals;
        },

        getSealDetails(sealId) {
            return this.sealsInfo.find(seal => seal.id === sealId) || {};
        },

        addSeal(proponentType, seal) {
            if (!this.proponentSeals[proponentType].includes(seal._id)) {
                this.proponentSeals[proponentType].push(seal._id);
                this.entityOpportunity.proponentSeals = this.proponentSeals;
            }

            this.entityOpportunity.save();  
        },

        removeSeal(proponentType, sealId) {
            this.proponentSeals[proponentType] = this.proponentSeals[proponentType].filter(id => id !== sealId);
            this.entityOpportunity.proponentSeals = this.proponentSeals;
            this.entityOpportunity.save();     
        },

        getSealQuery(proponentType) {
            const selectedSealIds = this.proponentSeals[proponentType].join(',');
            return selectedSealIds ? { id: `!IN(${selectedSealIds})` } : {};
        }
    }
});
