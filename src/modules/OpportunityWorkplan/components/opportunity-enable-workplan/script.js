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
        if (this.entity.workplan_monitoringInformDeliveryType == null) {
            this.entity.workplan_monitoringInformDeliveryType = [];
        }

        return {
            entity: this.entity,
            timeOut: null,
            workplan_monitoringInformDeliveryTypeList: Object.values($MAPAS.EntitiesDescription.opportunity.workplan_monitoringInformDeliveryType.options)
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
            }
        },
        'entity.workplan_metaLimitNumberOfGoals'(_new) {
            if (!_new) {
                this.entity.workplan_metaMaximumNumberOfGoals = 0;
            }
        },
        'entity.workplan_deliveryLimitNumberOfDeliveries'(_new) {
            if (!_new) {
                this.entity.workplan_deliveryMaximumNumberOfDeliveries = 0;
            }
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
        autoSave() {
            this.entity.save(3000);
        },
        disabledWorkPlan() {
            this.entity.workplan_dataProjectlimitMaximumDurationOfProjects = false;
            this.entity.workplan_dataProjectmaximumDurationInMonths = 0;

            this.entity.workplan_metaInformTheStageOfCulturalMaking = false;
            this.entity.workplan_metaInformTheValueGoals = false;
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
            this.entity.workplan_registrationInformActionPAAR = false;
            this.entity.workplan_registrationInformCulturalArtisticSegment = false;

            this.entity.workplan_monitoringInformTheFormOfAvailability = false;
            this.entity.workplan_monitoringEnterDeliverySubtype = false;
            this.entity.workplan_monitoringInformDeliveryType = [];
            this.entity.workplan_monitoringInformAccessibilityMeasures = false;
            this.entity.workplan_monitoringInformThePriorityTerritories = false;
            this.entity.workplan_monitoringProvideTheProfileOfParticipants = false;
            this.entity.workplan_monitoringInformThePriorityAudience = false;
            this.entity.workplan_monitoringReportExecutedRevenue = false;
        },
    },
})