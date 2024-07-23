app.component('qualification-evaluation-form', {
    template: $TEMPLATES['qualification-evaluation-form'],

    setup() {
        const messages = useMessages();
        const text = Utils.getTexts('qualification-evaluation-form');
        return { text, messages };
    },
    created() {
        this.formData['data'] = this.evaluationData || this.skeleton();
        this.formData.uid = this.userId;
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
            evaluationId: null,
        };
    },
    
    computed: {
        sections() {
            return $MAPAS.config.qualificationEvaluationForm.sections || [];

        },
        status() {
            return $MAPAS.config.qualificationEvaluationForm.evaluationData || [];
        },
        statusText() {
            const statusMap = {
                0: 'Não Enviada',
                1: 'Em andamento',
                2: 'Enviada',
            };

            return statusMap[this.status];
        },
        evaluationData() {
            const evaluation = $MAPAS.config.qualificationEvaluationForm.evaluationData;
            return evaluation && evaluation.evaluationData ? evaluation.evaluationData : {};
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
