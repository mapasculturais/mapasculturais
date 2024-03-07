
app.component('evaluation-method-technical--apply', {
    template: $TEMPLATES['evaluation-method-technical--apply'],

    props: {
        entity: {
            type: Entity,
            required: true
        }
    },

    data() {
        applyAll = false;
        let max = parseFloat($MAPAS.config.evaluationMethodTechnicalApply.max_result || "0.00");
        applyData = {
            from:[0,max],
            cutoffScore: 0,
            
        };

        return {
            applyData,
            applyAll,
            markSubstitute: false,
            deleteRegistrations: false,
            selectFirst: false,
            considerQuotas: false,
            selectionType: ''
        }
    },
    watch: {

    },

    computed: {
        statusList() {
            return $MAPAS.config.evaluationMethodTechnicalApply.registrationStatusDict;
        },
        maxResult() {
            return parseFloat($MAPAS.config.evaluationMethodTechnicalApply.max_result || "0.00");
        },
    },

    methods: {
        validateValues() {
            const min = 0;
            const max = this.maxResult;
            
            if (this.applyData.from[0] < min) {
                this.applyData.from[0] = min;
            }
            if (this.applyData.from[1] > max) {
                this.applyData.from[1] = max;
            }
        },

        modalClose() {
            delete this.applyData.to;
            this.applyData.from[0] = 0;
            this.applyData.from[1] = this.maxResult;
            this.selectionType = '';
        },

        apply(modal,entity) {
            this.applyData.status = this.applyData.applyAll ? 'all' : false;
            this.applyData.cutoffScore = this.entity.evaluationMethodConfiguration?.cutoffScore;
            this.applyData.selectFirsts = this.selectFirst;
            this.applyData.quantityVacancies = this.entity.vacancies;
            this.applyData.considerQuotas = this.considerQuotas;
            this.applyData.deleteRegistrations = this.deleteRegistrations;
            this.applyData.markSubstitute = this.markSubstitute;
            this.entity.disableMessages();
            this.entity.POST('appyTechnicalEvaluation', {
                data: this.applyData, callback: data => {
                    const messages = useMessages();
                    if (data.error) {
                        messages.error(data.data)
                    } else {
                        messages.success(data);
                        modal.close();
                        this.reloadPage();
                    }
                    this.entity.enableMessages();
                }
            })
        },
        
        reloadPage(timeout = 1500) {
            setTimeout(() => {
                document.location.reload(true)
            }, timeout);
        },

        initConsiderQuotas() {
            if (this.selectionType === 'first') {
                this.considerQuotas = true;
                this.selectFirst = true;
                this.markSubstitute = false;
                this.deleteRegistrations = false;
            } 

            if (this.selectionType === 'substitute') {
                this.considerQuotas = true;
                this.markSubstitute = true;
                this.selectFirst = false;
                this.deleteRegistrations = false;
            }

            if (this.selectionType === 'delRegistrations') {
                this.considerQuotas = false;
                this.markSubstitute = false;
                this.selectFirst = false;
                this.deleteRegistrations = true;
            }
        }

    },
});
