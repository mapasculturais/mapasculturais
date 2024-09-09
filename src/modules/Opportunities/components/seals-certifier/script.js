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

    data() {
        let proponentTypes = this.entity.opportunity.registrationProponentTypes;
        let proponentSeals = {};
        proponentTypes.forEach(type => {
            proponentSeals[type] = [];
        });
        
        return {
            proponentTypes,  
            proponentSeals,
        }
    },

    created() {
        this.proponentSeals = this.entity.opportunity.proponentSeals || this.proponentSeals;
    },

    methods: {
        getSealDetails(sealId) {
            return this.entity.opportunity.seals.find(seal => seal.sealId === sealId) || {};
        },

        addSeal(proponentType, seal) {
            if (!this.proponentSeals[proponentType].includes(seal._id)) {
                this.proponentSeals[proponentType].push(seal._id);
            }

            this.entity.opportunity.proponentSeals = this.proponentSeals;
            this.entity.opportunity.save();  
        },

        removeSeal(proponentType, sealId) {
            this.proponentSeals[proponentType] = this.proponentSeals[proponentType].filter(id => id !== sealId);
            this.entity.opportunity.proponentSeals = this.proponentSeals;
            this.entity.opportunity.save();     
        },

        getSealQuery(proponentType) {
            const selectedSealIds = this.proponentSeals[proponentType].join(',');
            return selectedSealIds ? { id: `!IN(${selectedSealIds})` } : {};
        }
    }
});
