app.component('qualification-evaluation-form', {
    template: $TEMPLATES['qualification-evaluation-form'],

    setup() {
        const messages = useMessages();
        const text = Utils.getTexts('qualification-evaluation-form');
        return { text, messages };
    },
    created() {
        this.formData['data'] = this.evaluationData || this.skeleton();
        this.handleCurrentEvaluationForm();
        this.formData.uid = this.userId;
    },

    props: {
        entity: {
            type: Object,
            required: true
        },
        editable: {
            type: Boolean,
            default: true
        },
    },

    data() {
        return {
            formData: {
                sectionStatus: {},
                obs: '',
                data: {},
            },
            superteste: [],
            isEditable: true,
            evaluationId: null,
        };
    },
    mounted() {
        window.addEventListener('responseEvaluation', this.processResponse);
        this.isEditable = this.status > 0 ? false : this.editable;
    },

    computed: {
        sections() {
            return $MAPAS.config.qualificationEvaluationForm.sections || [];
        },
        status() {
            return $MAPAS.config.qualificationEvaluationForm.evaluationData?.status || 0;
        },
        statusText() {
            const statusMap = {
                0: this.text('Not_sent'),
                1: this.text('In_progress'),
                2: this.text('Sent'),
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
        currentEvaluation() {
            return $MAPAS.config.qualificationEvaluationForm.currentEvaluation;
        },
    },

    methods: {
        handleChange(sectionId, criteriaId, event) {
            let section = {
                [sectionId]:{
                    [criteriaId]: event.value,
                }
            }
            this.superteste.push(section);
            this.testeSection();

            // this.sections.forEach((sec,index) => {
            //     if(sec.id == sectionId){
            //         sect.criteria.forEach((crit, critIndex) => {
            //             if(crit.id == criteriaId){
            //                 this.testeSection();
            //             }
            //         })
            //     }
            //     console.log('section ->',sect);
            // });

            // if(sectionId)
            
            return this.formData.sectionStatus;

        },
        testeSection(){
            this.superteste.forEach((test,index) => {
                console.log(test);
                console.log(index);
            });
        },
        validateErrors() {
            let isValid = false;
            this.errors = [];

            for (let sectionIndex in this.sections) {
                for (let crit of this.sections[sectionIndex].criteria) {
                    let sectionName = this.sections[sectionIndex].name;
                    let value = this.formData.data[crit.id];
                    if (!value || value === "") {
                        this.messages.error(`${this.text('on_section')} ${sectionName}, ${this.text('the_field')} ${crit.name} ${this.text('is_required')}`);
                        isValid = true;
                    }
                }
            }

            if (!this.formData.data.obs || this.formData.data.obs === "") {
                this.messages.error(this.text('technical-mandatory'));
                isValid = true;
            }

            return isValid;
        },
        processResponse(data) {
            if (data.detail.response.status > 0) {
                this.isEditable = false;
            } else {
                this.isEditable = true;
            }
        },

        handleCurrentEvaluationForm() {
            this.isEditable = this.currentEvaluation?.status > 0 ? false : this.editable;
        },
        skeleton() {
            return {
                uid: this.userId,
            };
        }
    },
});
