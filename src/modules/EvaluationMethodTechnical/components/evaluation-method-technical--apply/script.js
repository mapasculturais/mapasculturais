
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
        applyData = {
            from:[10,20]
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
