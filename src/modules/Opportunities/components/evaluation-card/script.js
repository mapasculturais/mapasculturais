app.component('evaluation-card', {
    template: $TEMPLATES['evaluation-card'],

    props: {
        entity: {
            type: [Entity, Object],
            required: true,
        },
        buttonLabel: {
            type: String,
        },
    },
    
    setup(props, { slots }) {
        const hasSlot = name => !!slots[name];
        const text = Utils.getTexts('evaluation-card');
        return { text, hasSlot }
    },

    data:() {
        return {
            formData: {}
        }
    },

    computed: {
        dateFrom() {
            if (this.entity.registrationFrom instanceof McDate) {
                return this.entity.registrationFrom;
            } else {
                return new McDate(this.entity.registrationFrom.date);
            }
        },

        dateTo() {
            if (this.entity.registrationTo instanceof McDate) {
                return this.entity.registrationTo;
            } else {
                return new McDate(this.entity.registrationTo.date);
            }
        },
    },
});
