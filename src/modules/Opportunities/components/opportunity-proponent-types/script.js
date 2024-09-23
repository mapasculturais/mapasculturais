app.component('opportunity-proponent-types', {
    template: $TEMPLATES['opportunity-proponent-types'],

    setup() {
        const text = Utils.getTexts('opportunity-proponent-types');
        return { text };
    },

    props: {
        entity: {
            type: Entity,
            required: true
        },
    },

    data() {
        let description = this.entity.$PROPERTIES.registrationProponentTypes || {};
        let value = this.entity.registrationProponentTypes || [];

        return {
            description,
            value,
            proponentTypesToAgentsMap: $MAPAS.config.opportunityProponentTypes,
            useAgentRelationColetivo: this.entity.useAgentRelationColetivo || 'dontUse',
            proponentAgentRelation: this.entity.proponentAgentRelation || {
                "Coletivo": false,
                "Pessoa Jurídica": false
            },
        };
    },

    computed: {
        showColetivoBinding() {
            return this.value.includes('Coletivo') && this.proponentTypesToAgentsMap['Coletivo'] === 'coletivo';
        },

        showJuridicaBinding() {
            return this.value.includes('Pessoa Jurídica') && this.proponentTypesToAgentsMap['Pessoa Jurídica'] === 'coletivo';
        }
    },

    methods: {
        modifyCheckbox(event) {
            const optionValue = event.target.value;
            const index = this.value.indexOf(optionValue);

            if (index === -1) {
                this.value.push(optionValue);
            } else {
                this.value.splice(index, 1);
    
                if (optionValue === 'Coletivo' || optionValue === 'Pessoa Jurídica') {
                    this.proponentAgentRelation[optionValue] = false;
                }
            }

            this.updateProponentAgentRelation();
            this.entity.save();
        },

        toggleAgentRelation(event, type) {
            this.proponentAgentRelation[type] = event.target.checked;
            this.updateProponentAgentRelation();
            this.entity.save();
        },

        updateProponentAgentRelation() {
            const anyAgentRelationChecked = Object.values(this.proponentAgentRelation).includes(true);
            this.entity.useAgentRelationColetivo = anyAgentRelationChecked ? 'required' : 'dontUse';
            this.entity.proponentAgentRelation = this.proponentAgentRelation;
        }
    }
});
