app.component('qualification-evaluation-form', {
    template: $TEMPLATES['qualification-evaluation-form'],
    
    setup() {
        const messages = useMessages();
        const text = Utils.getTexts('qualification-evaluation-form');
        return { text, messages };
    },

    props: {
        entity: {
            type: Object,
            required: true
        },
    },
    
    data() {
        return {
            sections: $MAPAS.config.qualificationEvaluationForm.sections || [],
        };
    },

    methods: {
    
    }
});
