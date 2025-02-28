app.component('registration-workplan-form', {
    template: $TEMPLATES['registration-workplan-form'],
    props: {
        editable: {
            type: Boolean,
            default: false,
        },
        phaseId: {
            type: Number,
            required: false,
        },
        registration: {
            type: Entity,
            required: false,
        },
    },
    data () {
        return {
            workplan: new Entity('workplan'),
        };
    },
    async created () {
        if (this.phaseId) {
            this.workplan = this.registrationModel.workplanSnapshot;
            return;
        }
        const api = new API('workplan');
        const response = await api.GET(String($MAPAS.config['registration-workplan-form'].parentRegistration));
        // const response = await api.GET(String(this.registration.id));
        const data = await response.json();
        if (data.workplan != null) {
            this.workplan = data.workplan;
        }
    },
    computed: {
        registrationModel () {
            if (this.registration) {
                return this.registration;
            } else {
                const registration = $MAPAS.registrationPhases[this.phaseId];
                return Vue.reactive(registration);
            }
        },
        workplansLabel () {
            const opportunity = this.registrationModel.opportunity.parent ?? this.registrationModel.opportunity;
            return opportunity.workplanLabelDefault ?? $MAPAS.EntitiesDescription.opportunity.workplanLabelDefault.default_value;
        },
    },
});
