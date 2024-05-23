app.component('simple-evaluation-form', {
    template: $TEMPLATES['simple-evaluation-form'],

    props: {
        entity: {
            type: Entity,
            required: true
        },

        editable: {
            type: Boolean,
            default: false
        },
    },

    setup() {
        const text = Utils.getTexts('simple-evaluation-form')
        return { text }
    },

    data() {
        return {
            formData: {},
        };
    },

    created() {
        this.formData = this.evaluationData || this.skeleton();
    },

    mounted() {

    },

    computed: {
        statusList() {
            return $MAPAS.config.simpleEvaluationForm.statusList;
        },

        userId() {
            return $MAPAS.config.simpleEvaluationForm.userId;
        },

        evaluationData() {
            return $MAPAS.config.simpleEvaluationForm.currentEvaluation?.evaluationData;
        },

        step() {
            return $MAPAS.config.simpleEvaluationForm.currentEvaluation?.status;
        }
    },

    methods: {
        handleOptionChange(selectedOption) {
            this.formData.status = selectedOption.value;
        },


        skeleton() {
            return {
                uid: this.userId,
                status: null,
                obs: null,
            };
        },
    },
});
