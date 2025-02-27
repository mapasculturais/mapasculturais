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
        const response = await api.GET(String($MAPAS.config['registration-workplan-form'].parentRegistration));
        // const response = await api.GET(String(this.registration.id));
        const data = await response.json();
        if (data.workplan != null) {
            this.workplan = data.workplan;
            this.registration.workplanProxy ??= this.createWorkplanProxy(data.workplan);
        }
    },
    computed: {
        workplansLabel () {
            const opportunity = this.registration.opportunity;
            return opportunity.workplanLabelDefault ?? $MAPAS.EntitiesDescription.opportunity.workplanLabelDefault.default_value;
        },
    },
    methods: {
        createWorkplanProxy (workplan) {
            const proxy = { goals: {}, deliveries: {} };

            const defaultGoal = {
                status: 0,
                executionDetail: '',
            };

            const defaultDelivery = {
                status: 0,
                accessibilityMeasures: [],
                availabilityType: '',
                evidenceLinks: [],
                executedRevenue: 0,
                numberOfParticipants: 0,
                participantProfile: '',
                priorityAudience: '', 
            }

            for (const goal of workplan.goals) {
                const proxyGoal = {};
                
                for (const key in defaultGoal) {
                    proxyGoal[key] = goal[key] ?? defaultGoal[key]; 
                }

                proxy.goals[goal.id] = proxyGoal;

                for (const delivery of goal.deliveries) {
                    const proxyDelivery = {};

                    for (const key in defaultDelivery) {
                        proxyDelivery[key] = delivery[key] ?? defaultDelivery[key];
                    }

                    proxy.deliveries[delivery.id] = proxyDelivery;
                }
            }

            return Vue.reactive(proxy);
        },
    }
});
