
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
            from:[0,max]
        };

        return {
            applyData,
            applyAll,
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
        resultnote() {
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
            
        },

        apply(modal,entity) {
            this.applyData.status = this.applyData.applyAll ? 'all' : false;
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
        }

    },
});
