app.component('simple-evaluation-form', {
    template: $TEMPLATES['simple-evaluation-form'],

    props: {
        entity: {
            type: Entity,
            required: true
        },

        editable: {
            type: Boolean,
            default: true
        },
    },

    setup() {
        const text = Utils.getTexts('simple-evaluation-form')
        return { text }
    },

    data() {
        return {
            formData: {},
            isEditable: true,
        };
    },

    created() {
        this.formData = this.evaluationData || this.skeleton();
        this.handleCurrentEvaluationForm();
    },

    mounted() {
        window.addEventListener('responseEvaluation', this.processResponse);
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

        currentEvaluation() {
            return $MAPAS.config.simpleEvaluationForm.currentEvaluation;
        },
    },

    methods: {
        handleOptionChange(selectedOption) {
            this.formData.status = selectedOption.value;
        },

        processResponse(data) {
            console.log(data.detail);
            if (data.detail.response.status > 0) {
                this.isEditable = false;
            }
        },

        handleCurrentEvaluationForm() {
            return this.currentEvaluation.status > 0 ? this.isEditable = false : this.isEditable = this.editable;
        },

        statusToString(status) {
            let result = '';
            this.statusList.forEach((item) => {
                if (item.value == status) {
                    result = item.label;
                }
            });

            return result;
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
