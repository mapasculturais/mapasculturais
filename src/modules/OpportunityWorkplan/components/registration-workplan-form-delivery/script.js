app.component('registration-workplan-form-delivery', {
    template: $TEMPLATES['registration-workplan-form-delivery'],
    props: {
        editable: {
            type: Boolean,
            default: false,
        },
        delivery: {
            type: Object,
            required: true,
        },
        registration: {
            type: Entity,
            required: true,
        },
    },
    data () {
        return {
            expanded: false,
        };
    },
    computed: {
        deliveriesLabel () {
            const opportunity = this.registration.opportunity;
            return opportunity.deliveryLabelDefault ?? $MAPAS.EntitiesDescription.opportunity.deliveryLabelDefault.default_value;
        },
        proxy () {
            return this.registration.workplanProxy.deliveries[this.delivery.id];
        },
        statusOptions () {
            return $MAPAS.config.deliveriesStatuses;
        },
    },
    methods: {
        convertToCurrency(field) {
            return new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(field);
        },
    }
});
