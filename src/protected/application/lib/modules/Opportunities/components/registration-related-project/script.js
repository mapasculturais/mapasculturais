app.component('registration-related-project', {
    template: $TEMPLATES['registration-related-project'],

    props: {
        registration: {
            type: Entity,
            required: true
        },
    },

    setup() {
        const text = Utils.getTexts('registration-related-project')
        return { text }
    },

    data() {
        return {
            opportunity: this.registration.opportunity,
        }
    },

    computed: {
        useProjectRelation() {
            const metadata = 'projectName';
            
            if (this.opportunity[metadata]) {
                switch (this.opportunity[metadata]) {
                    case 0: 
                        return 'dontUse';
                    case 1: 
                        return 'optional';
                    case 2: 
                        return 'required';
                }
            }
            return 'dontUse';
        },
    },
});
