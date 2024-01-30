
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
