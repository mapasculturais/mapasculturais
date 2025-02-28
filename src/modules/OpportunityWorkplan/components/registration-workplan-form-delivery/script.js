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
    setup () {
        const vid = Vue.useId();
        return { vid };
    },
    computed: {
        accessibilityOptions () {
            return Vue.markRaw($DESCRIPTIONS.delivery.accessibilityMeasures.options);
        },
        audienceOptions () {
            return Vue.markRaw($DESCRIPTIONS.delivery.priorityAudience.options);
        },
        availabilityOptions () {
            return Vue.markRaw($DESCRIPTIONS.delivery.availabilityType.options);
        },
        deliveriesLabel () {
            const opportunity = this.registration.opportunity.parent ?? this.registration.opportunity;
            return opportunity.deliveryLabelDefault ?? Vue.markRaw($MAPAS.EntitiesDescription.opportunity.deliveryLabelDefault.default_value);
        },
        evidenceLinks: {
            get () {
                return this.proxy.evidenceLinks ?? [];
            },
            set (value) {
                this.proxy.evidenceLinks = value;
            },
        },
        executedRevenue: {
            get () {
                return this.proxy.executedRevenue?.scalar ?? 0;
            },
            set (value) {
                this.proxy.executedRevenue = { scalar: value };
            },
        },
        opportunity () {
            return this.registration.opportunity.parent ?? this.registration.opportunity;
        },
        proxy () {
            return this.registration.workplanProxy.deliveries[this.delivery.id];
        },
        statusOptions () {
            return Vue.markRaw($MAPAS.config.deliveriesStatuses);
        },
        validationErrors () {
            return this.registration.__validationErrors?.workplanProxy?.deliveries[this.delivery.id] ?? {};
        },
    },
    methods: {
        convertToCurrency(field) {
            return new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(field);
        },
    }
});
