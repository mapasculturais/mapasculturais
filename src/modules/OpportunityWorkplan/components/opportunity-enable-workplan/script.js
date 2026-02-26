app.component('opportunity-enable-workplan', {
    template: $TEMPLATES['opportunity-enable-workplan'],

    setup() {
        const text = Utils.getTexts('opportunity-enable-workplan');
        return { text };
    },
    props: {
        entity: {
            type: Entity,
            required: true
        }
    },
    data() {
        return {
            timeOut: null
        }
    },
    watch: {
        'entity.enableWorkplan'(_new) {
            if (!_new) {
                this.disabledWorkPlan();
            }
        },
        'entity.workplan_dataProjectlimitMaximumDurationOfProjects'(_new) {
            if (!_new) {
                this.entity.workplan_dataProjectmaximumDurationInMonths = 0;
            } else {
                this.entity.workplan_dataProjectmaximumDurationInMonths = 1;
            }
            this.autoSave();
        },
        'entity.workplan_metaLimitNumberOfGoals'(_new) {
            if (!_new) {
                this.entity.workplan_metaMaximumNumberOfGoals = 0;
            } else {
                this.entity.workplan_metaMaximumNumberOfGoals = 1;
            }
            this.autoSave();
        },
        'entity.workplan_deliveryLimitNumberOfDeliveries'(_new) {
            if (!_new) {
                this.entity.workplan_deliveryMaximumNumberOfDeliveries = 0;
            } else {
                this.entity.workplan_deliveryMaximumNumberOfDeliveries = 1;
            }
            this.autoSave();
        },
        'entity.workplan_deliveryReportTheDeliveriesLinkedToTheGoals'(_new) {
            if (!_new) {
                this.disabledDeliveries();
            }
        },
    },
    computed: {
        getWorkplanLabelDefault() {
            return this.entity.workplanLabelDefault ? this.entity.workplanLabelDefault : $MAPAS.EntitiesDescription.opportunity.workplanLabelDefault.default_value;
        },
        getGoalLabelDefault() {
            return this.entity.goalLabelDefault ? this.entity.goalLabelDefault : $MAPAS.EntitiesDescription.opportunity.goalLabelDefault.default_value;
        },
        getDeliveryLabelDefault() {
            return this.entity.deliveryLabelDefault ? this.entity.deliveryLabelDefault : $MAPAS.EntitiesDescription.opportunity.deliveryLabelDefault.default_value;
        },
    },
    methods: {
        changeLabels(modal) {
            this.autoSave();
            modal.close();            
        },
        actionEnabledWorkplan() {
            this.entity.enableWorkplan = true;
            this.entity.save();
        },
        actionDisabledWorkplan() {
            this.entity.enableWorkplan = false;
            this.entity.save();
        },
        autoSave() {
            this.entity.save(3000);
        },
        disabledWorkPlan() {
            this.entity.workplan_dataProjectlimitMaximumDurationOfProjects = false;
            this.entity.workplan_dataProjectmaximumDurationInMonths = 0;
            this.entity.workplan_dataProjectInformCulturalArtisticSegment = false;

            this.entity.workplan_metaInformTheStageOfCulturalMaking = false;
            this.entity.workplan_metaLimitNumberOfGoals = false;
            this.entity.workplan_metaMaximumNumberOfGoals = 0;

            this.entity.workplan_deliveryReportTheDeliveriesLinkedToTheGoals = false;

            this.disabledDeliveries();
        },
        disabledDeliveries() {
            this.entity.workplan_deliveryLimitNumberOfDeliveries = false;
            this.entity.workplan_deliveryMaximumNumberOfDeliveries = 0;
            this.entity.workplan_registrationReportTheNumberOfParticipants = false;
            this.entity.workplan_registrationReportExpectedRenevue = false;
            this.entity.workplan_registrationInformCulturalArtisticSegment = false;

            this.entity.workplan_monitoringInformTheFormOfAvailability = false;
            this.entity.workplan_monitoringInformAccessibilityMeasures = false;
            this.entity.workplan_monitoringProvideTheProfileOfParticipants = false;
            this.entity.workplan_monitoringInformThePriorityAudience = false;
            this.entity.workplan_monitoringReportExecutedRevenue = false;
        },
        pluralParaSingular(texto) {
            const palavras = texto.split(' ');
        
            const palavrasNoSingular = palavras.map(palavra => {
                if (palavra.endsWith('s')) {
                    palavra = palavra.slice(0, -1);
        
                    if (palavra.endsWith('e')) {
                        palavra = palavra.slice(0, -1);
                    }
        
                    if (palavra.endsWith('ã')) {
                        palavra = palavra.slice(0, -1) + 'ão';
                    } else if (palavra.endsWith('õ')) {
                        palavra = palavra.slice(0, -1) + 'ão';
                    } else if (palavra.endsWith('is')) {
                        palavra = palavra.slice(0, -2) + 'il';
                    } else if (palavra.endsWith('ns')) {
                        palavra = palavra.slice(0, -2) + 'm';
                    } else if (palavra.endsWith('ões')) {
                        palavra = palavra.slice(0, -3) + 'ão';
                    } else if (palavra.endsWith('ães')) {
                        palavra = palavra.slice(0, -3) + 'ão';
                    } else if (palavra.endsWith('ais')) {
                        palavra = palavra.slice(0, -2) + 'al';
                    } else if (palavra.endsWith('éis')) {
                        palavra = palavra.slice(0, -2) + 'el';
                    } else if (palavra.endsWith('óis')) {
                        palavra = palavra.slice(0, -2) + 'ol';
                    } else if (palavra.endsWith('uis')) {
                        palavra = palavra.slice(0, -2) + 'ul';
                    } else if (palavra.endsWith('ões')) {
                        palavra = palavra.slice(0, -3) + 'ão';
                    } else if (palavra.endsWith('ães')) {
                        palavra = palavra.slice(0, -3) + 'ão';
                    }
                }
        
                return palavra;
            });
        
            return palavrasNoSingular.join(' ');
        }
    },
})