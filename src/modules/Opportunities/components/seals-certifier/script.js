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
            sealsInfo: $MAPAS.config.sealsCertifier.seals,
            entityOpportunity: (this.entity.__objectType === 'opportunity') ? this.entity : (this.entity.opportunity || {}),
        }
    },

    mounted() {
        this.initializeProponentSeals();
    },

    watch: {
        proponentTypes: {
            handler() {
                this.syncProponentSeals();
            },
            deep: true
        }
    },

    computed: {
        proponentTypes() {
            return this.entityOpportunity.registrationProponentTypes.length > 0
                ? this.entityOpportunity.registrationProponentTypes
                : ['default'];
        },
    },

    methods: {
        initializeProponentSeals() {
            this.proponentTypes.forEach(proponentType => {
                if (!this.entityOpportunity.proponentSeals) {
                    this.entityOpportunity.proponentSeals = this.skeleton();
                }

                this.proponentSeals[proponentType] = this.entityOpportunity.proponentSeals[proponentType] || [];
            });
        },

        skeleton() {
            const seals = {};

            for (let proponentType of this.proponentTypes) {
                seals[proponentType] = [];
            }

            return seals;
        },

        syncProponentSeals() {
            const newProponentTypes = this.proponentTypes;
            const oldProponentTypes = Object.keys(this.proponentSeals);

            for (let proponentType of newProponentTypes) {
                if (!oldProponentTypes.includes(proponentType)) {
                    this.proponentSeals[proponentType] = [];
                }
            }

            for (let proponentType of oldProponentTypes) {
                if (!newProponentTypes.includes(proponentType)) {
                    delete this.proponentSeals[proponentType];
                }
            }

            this.entityOpportunity.proponentSeals = { ...this.proponentSeals };
        },

        getSealDetails(sealId) {
            return this.sealsInfo.find(seal => seal.id === sealId) || {};
        },

        modifySealList(proponentType, sealId, action) {
            const seals = this.proponentSeals[proponentType];
            const index = seals.indexOf(sealId);

            if (action === 'add' && index === -1) {
                seals.push(sealId);
            } else if (action === 'remove' && index !== -1) {
                seals.splice(index, 1);
            }

            this.entityOpportunity.proponentSeals = { ...this.proponentSeals };
            this.entityOpportunity.save();
        },

        addSeal(proponentType, seal) {
            this.modifySealList(proponentType, seal._id, 'add');
        },

        removeSeal(proponentType, sealId) {
            this.modifySealList(proponentType, sealId, 'remove');
        },

        getSealQuery(proponentType) {
            const selectedSealIds = this.proponentSeals[proponentType].join(',');
            return selectedSealIds ? { id: `!IN(${selectedSealIds})` } : {};
        }
    }
});
