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
        }
    },

    methods: {
        handleOptionChange(selectedOption) {
            this.formData.status = selectedOption.value;
        },

        saveEvaluation() {
            const messages = useMessages();
            const api = new API('registration');
            let url = api.createUrl('saveEvaluation', { id: this.entity.id });
            if (!this.validateErrors(this.formData)) {
                api.POST(url, {data: this.formData}).then(res => res.json()).then(response => {
                    messages.success(this.text('success'));
                });
            }
        },

        validateErrors(data) {
            const messages = useMessages();
            let error = false;
            Object.keys(data).forEach(key => { 
                if (!this.formData[key] || this.formData[key] === '') {
                    messages.error(this.text('emptyField') + ' ' + this.text(this.dictFields(key)) + ' ' + this.text('required'));
                    error = true;
                }
            });
            return error;
        },

        skeleton() {
            return {
                uid: this.userId,
                status: null,
                obs: null,
            };
        },

        dictFields(field) {
            const fields = {
                status: this.text('field_status_name'),
                obs: this.text('field_obs_name'),
                uid: this.text('field_uid_name'),
            };

            return fields[field];
        }
    },
});
