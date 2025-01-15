app.component('registration-details-workplan', {
    template: $TEMPLATES['registration-details-workplan'],
    setup() {
        const text = Utils.getTexts('registration-details-workplan');
        return { text };
    },
    props: {
        registration: {
            type: Entity,
            required: true
        },
    },
    data() {
        this.getWorkplan();

        const entityWorkplan = new Entity('workplan');

        return {
            opportunity: this.registration.opportunity,
            workplan: entityWorkplan,
        };
    },
    computed: {
        getWorkplanLabelDefault() {
            return this.opportunity.workplanLabelDefault ? this.opportunity.workplanLabelDefault : $MAPAS.EntitiesDescription.opportunity.workplanLabelDefault.default_value;
        },
        getGoalLabelDefault() {
            return this.opportunity.goalLabelDefault ? this.opportunity.goalLabelDefault : $MAPAS.EntitiesDescription.opportunity.goalLabelDefault.default_value;
        },
        getDeliveryLabelDefault() {
            return this.opportunity.deliveryLabelDefault ? this.opportunity.deliveryLabelDefault : $MAPAS.EntitiesDescription.opportunity.deliveryLabelDefault.default_value;
        },
    },
    methods: {
        getWorkplan() {
            const api = new API('workplan');
            
            const response = api.GET(`${this.registration.id}`);
            response.then((res) => res.json().then((data) => {
                if (data.workplan != null) {
                    this.workplan = data.workplan;
                }
            }));
        },
        convertToCurrency(field) {
            return new Intl.NumberFormat("pt-BR", {
                style: "currency",
                currency: "BRL"
              }).format(field);
        }
    },
})