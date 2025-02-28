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
        }
    },
    computed: {
        workplansLabel () {
            const opportunity = this.registration.opportunity.parent;
            return opportunity.workplanLabelDefault ?? $MAPAS.EntitiesDescription.opportunity.workplanLabelDefault.default_value;
        },
    },
});
