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
        console.log(value);
        console.log(description);

        return {
            description,
            value,
            proponentTypesToAgentsMap: $MAPAS.config.opportunityProponentTypes,
            useAgentRelationColetivo: this.entity.useAgentRelationColetivo || 'dontUse',
            showColetivoBinding: false,
            showJuridicaBinding: false,
        };
    },

    methods: {
        modifyCheckbox(event) {
            if (!this.value) {
                this.value = [];
            } else if (typeof this.value !== 'object') {
                this.value = this.value.split(";");
            }

            let index = this.value.indexOf(event.target.value);
            if (index === -1) {
                this.value.push(event.target.value);
            } else {
                this.value.splice(index, 1);
            }

            // Atualiza para "dontUse" se qualquer checkbox é marcado ou desmarcado
            this.entity.useAgentRelationColetivo = 'dontUse';

            // Controle de exibição
            this.showColetivoBinding = this.value.includes('Coletivo') && this.proponentTypesToAgentsMap['Coletivo'] === 'coletivo';
            this.showJuridicaBinding = this.value.includes('Pessoa Jurídica') && this.proponentTypesToAgentsMap['Pessoa Jurídica'] === 'coletivo';

            this.entity.save();
        },

        toggleAgentRelation(event) {
            if (event.target.checked) {
                this.entity.useAgentRelationColetivo = 'required';  // Muda para "required" ao marcar o checkbox adicional
            } else {
                this.entity.useAgentRelationColetivo = 'dontUse';  // Volta para "dontUse" ao desmarcar
            }
            this.entity.save();
        },
    }
});
