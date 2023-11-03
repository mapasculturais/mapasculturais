app.component('evaluation-card', {
    template: $TEMPLATES['evaluation-card'],

    props: {
        entity: {
            type: [Entity, Object],
            required: true,
        }
    },
    
    setup(props, { slots }) {
        const hasSlot = name => !!slots[name];
        const text = Utils.getTexts('evaluation-card');
        return { text, hasSlot }
    },

    computed: {
        dateFrom() {
            return new McDate(this.entity.registrationFrom.date);
        },

        dateTo() {
            return new McDate(this.entity.registrationTo.date);
        },
    },
});
