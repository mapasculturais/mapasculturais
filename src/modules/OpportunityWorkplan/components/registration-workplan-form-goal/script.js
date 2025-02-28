app.component('registration-workplan-form-goal', {
    template: $TEMPLATES['registration-workplan-form-goal'],
    props: {
        editable: {
            type: Boolean,
            default: false,
        },
        goal: {
            type: Object,
            required: true,
        },
        index: {
            type: Number,
            default: 0,
        },
        registration: {
            type: Entity,
            required: true,
        },
    },
    setup () {
        const vid = Vue.useId();
        return { vid };
    },
    data () {
        return {
            expanded: this.index === 0,
        };
    },
    computed: {
        goalsLabel () {
            const opportunity = this.registration.opportunity.parent;
            return opportunity.goalLabelDefault ?? Vue.markRaw($MAPAS.EntitiesDescription.opportunity.goalLabelDefault.default_value);
        },
        proxy () {
            return this.registration.workplanProxy.goals[this.goal.id];
        },
        statusOptions () {
            return Vue.markRaw($MAPAS.config.goalsStatuses);
        },
    }
});
