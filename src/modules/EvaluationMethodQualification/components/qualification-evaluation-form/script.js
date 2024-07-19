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
            formData: {
                sectionStatus: {},
                obs: '',
                data: {},

            },
        };
    },
    computed: {
        sections() {
            return $MAPAS.config.qualificationEvaluationForm.sections || [];
        },
        userId() {
            return $MAPAS.userId;
        },
        entityId() {
            return this.entity && this.entity.id ? this.entity.id : null;
        }
    },
    mounted() {
        this.fetchEvaluations();
    },

    methods: {
    
    }
});
