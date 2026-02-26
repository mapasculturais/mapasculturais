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
        // Watchers para resetar campos "require" quando "inform" é desmarcado
        // Workplan
        'entity.workplan_dataProjectInformCulturalArtisticSegment'(_new) {
            if (!_new) this.entity.workplan_dataProjectRequireCulturalArtisticSegment = false;
        },
        // Goal
        'entity.workplan_goalInformTitle'(_new) {
            if (!_new) this.entity.workplan_goalRequireTitle = false;
        },
        'entity.workplan_goalInformDescription'(_new) {
            if (!_new) this.entity.workplan_goalRequireDescription = false;
        },
        // Delivery - Planejamento (campos originais)
        'entity.workplan_registrationReportTheNumberOfParticipants'(_new) {
            if (!_new) this.entity.workplan_deliveryRequireExpectedNumberPeople = false;
        },
        'entity.workplan_registrationInformCulturalArtisticSegment'(_new) {
            if (!_new) this.entity.workplan_deliveryRequireSegment = false;
        },
        // Delivery - Planejamento (novos campos)
        'entity.workplan_deliveryInformArtChainLink'(_new) {
            if (!_new) this.entity.workplan_deliveryRequireArtChainLink = false;
        },
        'entity.workplan_deliveryInformTotalBudget'(_new) {
            if (!_new) this.entity.workplan_deliveryRequireTotalBudget = false;
        },
        'entity.workplan_deliveryInformNumberOfCities'(_new) {
            if (!_new) this.entity.workplan_deliveryRequireNumberOfCities = false;
        },
        'entity.workplan_deliveryInformNumberOfNeighborhoods'(_new) {
            if (!_new) this.entity.workplan_deliveryRequireNumberOfNeighborhoods = false;
        },
        'entity.workplan_deliveryInformMediationActions'(_new) {
            if (!_new) this.entity.workplan_deliveryRequireMediationActions = false;
        },
        'entity.workplan_deliveryInformPaidStaffByRole'(_new) {
            if (!_new) this.entity.workplan_deliveryRequirePaidStaffByRole = false;
        },
        'entity.workplan_deliveryInformTeamComposition'(_new) {
            if (!_new) {
                this.entity.workplan_deliveryRequireTeamCompositionGender = false;
                this.entity.workplan_deliveryRequireTeamCompositionRace = false;
            }
        },
        'entity.workplan_deliveryInformRevenueType'(_new) {
            if (!_new) this.entity.workplan_deliveryRequireRevenueType = false;
        },
        'entity.workplan_deliveryInformCommercialUnits'(_new) {
            if (!_new) {
                this.entity.workplan_deliveryRequireCommercialUnits = false;
                this.entity.workplan_deliveryRequireUnitPrice = false;
            }
        },
        'entity.workplan_deliveryInformCommunityCoauthors'(_new) {
            if (!_new) this.entity.workplan_deliveryRequireCommunityCoauthorsDetail = false;
        },
        'entity.workplan_deliveryInformTransInclusion'(_new) {
            if (!_new) this.entity.workplan_deliveryRequireTransInclusionActions = false;
        },
        'entity.workplan_deliveryInformAccessibilityPlan'(_new) {
            if (!_new) this.entity.workplan_deliveryRequireExpectedAccessibilityMeasures = false;
        },
        'entity.workplan_deliveryInformEnvironmentalPractices'(_new) {
            if (!_new) this.entity.workplan_deliveryRequireEnvironmentalPracticesDescription = false;
        },
        'entity.workplan_deliveryInformCommunicationChannels'(_new) {
            if (!_new) this.entity.workplan_deliveryRequireCommunicationChannels = false;
        },
        'entity.workplan_deliveryInformInnovation'(_new) {
            if (!_new) this.entity.workplan_deliveryRequireInnovationTypes = false;
        },
        'entity.workplan_deliveryInformDocumentationTypes'(_new) {
            if (!_new) this.entity.workplan_deliveryRequireDocumentationTypes = false;
        },
        // Monitoramento - Campos originais
        'entity.workplan_monitoringInformTheFormOfAvailability'(_new) {
            if (!_new) this.entity.workplan_monitoringRequireAvailabilityType = false;
        },
        'entity.workplan_monitoringInformAccessibilityMeasures'(_new) {
            if (!_new) this.entity.workplan_monitoringRequireAccessibilityMeasures = false;
        },
        'entity.workplan_monitoringProvideTheProfileOfParticipants'(_new) {
            if (!_new) this.entity.workplan_monitoringRequireParticipantProfile = false;
        },
        'entity.workplan_monitoringInformThePriorityAudience'(_new) {
            if (!_new) this.entity.workplan_monitoringRequirePriorityAudience = false;
        },
        'entity.workplan_monitoringReportExecutedRevenue'(_new) {
            if (!_new) this.entity.workplan_monitoringRequireExecutedRevenue = false;
        },
        // Monitoramento - Novos campos
        'entity.workplan_monitoringInformNumberOfCities'(_new) {
            if (!_new) this.entity.workplan_monitoringRequireNumberOfCities = false;
        },
        'entity.workplan_monitoringInformNumberOfNeighborhoods'(_new) {
            if (!_new) this.entity.workplan_monitoringRequireNumberOfNeighborhoods = false;
        },
        'entity.workplan_monitoringInformMediationActions'(_new) {
            if (!_new) this.entity.workplan_monitoringRequireMediationActions = false;
        },
        'entity.workplan_monitoringInformCommercialUnits'(_new) {
            if (!_new) {
                this.entity.workplan_monitoringRequireCommercialUnits = false;
                this.entity.workplan_monitoringRequireUnitPrice = false;
            }
        },
        'entity.workplan_monitoringInformPaidStaffByRole'(_new) {
            if (!_new) this.entity.workplan_monitoringRequirePaidStaffByRole = false;
        },
        'entity.workplan_monitoringInformTeamComposition'(_new) {
            if (!_new) {
                this.entity.workplan_monitoringRequireTeamCompositionGender = false;
                this.entity.workplan_monitoringRequireTeamCompositionRace = false;
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
            // Duração do projeto
            this.entity.workplan_dataProjectlimitMaximumDurationOfProjects = false;
            this.entity.workplan_dataProjectmaximumDurationInMonths = 0;
            this.entity.workplan_dataProjectInformCulturalArtisticSegment = false;
            this.entity.workplan_dataProjectRequireCulturalArtisticSegment = false;

            // Metas
            this.entity.workplan_metaInformTheStageOfCulturalMaking = false;
            this.entity.workplan_metaLimitNumberOfGoals = false;
            this.entity.workplan_metaMaximumNumberOfGoals = 0;
            this.entity.workplan_goalInformTitle = false;
            this.entity.workplan_goalRequireTitle = false;
            this.entity.workplan_goalInformDescription = false;
            this.entity.workplan_goalRequireDescription = false;

            // Entregas
            this.entity.workplan_deliveryReportTheDeliveriesLinkedToTheGoals = false;

            this.disabledDeliveries();
        },
        disabledDeliveries() {
            // Limites de entregas
            this.entity.workplan_deliveryLimitNumberOfDeliveries = false;
            this.entity.workplan_deliveryMaximumNumberOfDeliveries = 0;

            // Inscrição - Campos originais
            this.entity.workplan_registrationReportTheNumberOfParticipants = false;
            this.entity.workplan_deliveryRequireExpectedNumberPeople = false;
            this.entity.workplan_registrationReportExpectedRenevue = false;
            this.entity.workplan_registrationInformCulturalArtisticSegment = false;
            this.entity.workplan_deliveryRequireSegment = false;

            // Inscrição - Novos campos
            this.entity.workplan_deliveryInformArtChainLink = false;
            this.entity.workplan_deliveryRequireArtChainLink = false;
            this.entity.workplan_deliveryInformTotalBudget = false;
            this.entity.workplan_deliveryRequireTotalBudget = false;
            this.entity.workplan_deliveryInformNumberOfCities = false;
            this.entity.workplan_deliveryRequireNumberOfCities = false;
            this.entity.workplan_deliveryInformNumberOfNeighborhoods = false;
            this.entity.workplan_deliveryRequireNumberOfNeighborhoods = false;
            this.entity.workplan_deliveryInformMediationActions = false;
            this.entity.workplan_deliveryRequireMediationActions = false;
            this.entity.workplan_deliveryInformPaidStaffByRole = false;
            this.entity.workplan_deliveryRequirePaidStaffByRole = false;
            this.entity.workplan_deliveryInformTeamComposition = false;
            this.entity.workplan_deliveryRequireTeamCompositionGender = false;
            this.entity.workplan_deliveryRequireTeamCompositionRace = false;
            this.entity.workplan_deliveryInformRevenueType = false;
            this.entity.workplan_deliveryRequireRevenueType = false;
            this.entity.workplan_deliveryInformCommercialUnits = false;
            this.entity.workplan_deliveryRequireCommercialUnits = false;
            this.entity.workplan_deliveryRequireUnitPrice = false;
            this.entity.workplan_deliveryInformCommunityCoauthors = false;
            this.entity.workplan_deliveryRequireCommunityCoauthorsDetail = false;
            this.entity.workplan_deliveryInformTransInclusion = false;
            this.entity.workplan_deliveryRequireTransInclusionActions = false;
            this.entity.workplan_deliveryInformAccessibilityPlan = false;
            this.entity.workplan_deliveryRequireExpectedAccessibilityMeasures = false;
            this.entity.workplan_deliveryInformEnvironmentalPractices = false;
            this.entity.workplan_deliveryRequireEnvironmentalPracticesDescription = false;
            this.entity.workplan_deliveryInformPressStrategy = false;
            this.entity.workplan_deliveryInformCommunicationChannels = false;
            this.entity.workplan_deliveryRequireCommunicationChannels = false;
            this.entity.workplan_deliveryInformInnovation = false;
            this.entity.workplan_deliveryRequireInnovationTypes = false;
            this.entity.workplan_deliveryInformDocumentationTypes = false;
            this.entity.workplan_deliveryRequireDocumentationTypes = false;

            // Monitoramento - Campos originais
            this.entity.workplan_monitoringInformTheFormOfAvailability = false;
            this.entity.workplan_monitoringRequireAvailabilityType = false;
            this.entity.workplan_monitoringInformAccessibilityMeasures = false;
            this.entity.workplan_monitoringRequireAccessibilityMeasures = false;
            this.entity.workplan_monitoringProvideTheProfileOfParticipants = false;
            this.entity.workplan_monitoringRequireParticipantProfile = false;
            this.entity.workplan_monitoringInformThePriorityAudience = false;
            this.entity.workplan_monitoringRequirePriorityAudience = false;
            this.entity.workplan_monitoringReportExecutedRevenue = false;
            this.entity.workplan_monitoringRequireExecutedRevenue = false;

            // Monitoramento - Novos campos
            this.entity.workplan_monitoringInformNumberOfCities = false;
            this.entity.workplan_monitoringRequireNumberOfCities = false;
            this.entity.workplan_monitoringInformNumberOfNeighborhoods = false;
            this.entity.workplan_monitoringRequireNumberOfNeighborhoods = false;
            this.entity.workplan_monitoringInformMediationActions = false;
            this.entity.workplan_monitoringRequireMediationActions = false;
            this.entity.workplan_monitoringInformCommercialUnits = false;
            this.entity.workplan_monitoringRequireCommercialUnits = false;
            this.entity.workplan_monitoringRequireUnitPrice = false;
            this.entity.workplan_monitoringInformPaidStaffByRole = false;
            this.entity.workplan_monitoringRequirePaidStaffByRole = false;
            this.entity.workplan_monitoringInformTeamComposition = false;
            this.entity.workplan_monitoringRequireTeamCompositionGender = false;
            this.entity.workplan_monitoringRequireTeamCompositionRace = false;
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