app.component('registration-workplan-form', {
    template: $TEMPLATES['registration-workplan-form'],
    props: {
        editable: {
            type: Boolean,
            default: false,
        },
        registration: {
            type: Entity,
            required: true,
        },
    },
    data () {
        return {
            workplan: new Entity('workplan'),
        };
    },
    async created () {
        const api = new API('workplan');
        const response = await api.GET(String(this.registration.id));
        const data = await response.json();
        if (data.workplan != null) {
            this.workplan = data.workplan;
            this.registration.workplanProxy = this.createWorkplanProxy(data.workplan);
        }
    },
    computed: {
        deliveriesLabel () {
            const opportunity = this.registration.opportunity;
            return opportunity.deliveryLabelDefault ?? $MAPAS.EntitiesDescription.opportunity.deliveryLabelDefault.default_value;
        },
        goalsLabel () {
            const opportunity = this.registration.opportunity;
            return opportunity.goalLabelDefault ?? $MAPAS.EntitiesDescription.opportunity.goalLabelDefault.default_value;
        },
        workplansLabel () {
            const opportunity = this.registration.opportunity;
            return opportunity.workplanLabelDefault ?? $MAPAS.EntitiesDescription.opportunity.workplanLabelDefault.default_value;
        },
    },
    methods: {
        createWorkplanProxy (workplan) {
            const proxy = { goals: {}, deliveries: {} };

            for (const goal of workplan.goals) {
                proxy.goals[goal.id] = {};

                for (const delivery of goal.deliveries) {
                    proxy.deliveries[delivery.id] = {};
                }
            }

            return Vue.reactive(proxy);
        },
    }
});
