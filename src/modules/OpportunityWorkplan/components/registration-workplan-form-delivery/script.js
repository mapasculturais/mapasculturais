app.component('registration-workplan-form-delivery', {
    template: $TEMPLATES['registration-workplan-form-delivery'],
    props: {
        editable: {
            type: Boolean,
            default: false,
        },
        delivery: {
            type: Object,
            required: true,
        },
        registration: {
            type: Entity,
            required: true,
        },
    },
    data () {
        return {
            expanded: false,
        };
    },
    setup () {
        const vid = Vue.useId();
        return { vid };
    },
    computed: {
        deliveryDescriptions () {
            return $DESCRIPTIONS.delivery ?? $MAPAS.EntitiesDescription.workplan?.goal?.delivery ?? {};
        },
        accessibilityMeasures: {
            get () {
                if (!this.proxy.accessibilityMeasures) {
                    return [];
                } else if (typeof this.proxy.accessibilityMeasures === 'string') {
                    return JSON.parse(this.proxy.accessibilityMeasures) ?? [];
                }
                return this.proxy.accessibilityMeasures;
            },
            set (value) {
                this.proxy.accessibilityMeasures = value;
            }
        },
        accessibilityOptions () {
            return Vue.markRaw(this.deliveryDescriptions.accessibilityMeasures?.options ?? {});
        },
        audienceOptions () {
            return Vue.markRaw(this.deliveryDescriptions.priorityAudience?.options ?? {});
        },
        availabilityOptions () {
            return Vue.markRaw(this.deliveryDescriptions.availabilityType?.options ?? {});
        },
        deliveriesLabel () {
            const opportunity = this.registration.opportunity.parent ?? this.registration.opportunity;
            return opportunity.deliveryLabelDefault ?? Vue.markRaw($MAPAS.EntitiesDescription.opportunity.deliveryLabelDefault.default_value);
        },
        dummyEntity () {
            // A dummy entity, just for uploads so far
            const entity = new Entity('delivery');
            entity.populate(this.delivery);
            return Vue.reactive(entity);
        },
        evidenceLinks: {
            get () {
                if (!this.proxy.evidenceLinks) {
                    return [];
                } else if (typeof this.proxy.evidenceLinks === 'string') {
                    return JSON.parse(this.proxy.evidenceLinks) ?? [];
                }
                return this.proxy.evidenceLinks;
            },
            set (value) {
                this.proxy.evidenceLinks = value;
            },
        },
        executedRevenue: {
            get () {
                return this.proxy.executedRevenue?.scalar ?? 0;
            },
            set (value) {
                this.proxy.executedRevenue = { scalar: value };
            },
        },
        opportunity () {
            return this.registration.opportunity.parent ?? this.registration.opportunity;
        },
        plannedCommunicationChannels () {
            return this.normalizeArray(this.delivery.communicationChannels);
        },
        plannedDocumentationTypes () {
            return this.normalizeArray(this.delivery.documentationTypes);
        },
        plannedExpectedAccessibilityMeasures () {
            return this.normalizeArray(this.delivery.expectedAccessibilityMeasures);
        },
        plannedInnovationTypes () {
            return this.normalizeArray(this.delivery.innovationTypes);
        },
        plannedPaidStaffByRole () {
            return this.normalizeArray(this.delivery.paidStaffByRole);
        },
        plannedRevenueType () {
            return this.normalizeArray(this.delivery.revenueType);
        },
        plannedTeamCompositionGender () {
            return this.normalizeObject(this.delivery.teamCompositionGender);
        },
        plannedTeamCompositionRace () {
            return this.normalizeObject(this.delivery.teamCompositionRace);
        },
        priorityAudience: {
            get () {
                if (!this.proxy.priorityAudience) {
                    return [];
                } else if (typeof this.proxy.priorityAudience === 'string') {
                    return JSON.parse(this.proxy.priorityAudience) ?? [];
                }
                return this.proxy.priorityAudience;
            },
            set (value) {
                this.proxy.priorityAudience = value;
            }
        },
        proxy () {
            if (this.editable) {
                return this.registration.workplanProxy.deliveries[this.delivery.id];
            } else {
                return this.delivery;
            }
        },
        statusOptions () {
            return Vue.markRaw($MAPAS.config.deliveriesStatuses);
        },
        validationErrors () {
            return this.registration.__validationErrors?.workplanProxy?.deliveries[this.delivery.id] ?? {};
        },

        // ── Novos campos executados ──────────────────────────────────────
        artChainLinkOptions () {
            return Vue.markRaw(this.deliveryDescriptions.artChainLink?.options ?? []);
        },
        booleanOptions () {
            return Vue.markRaw(this.deliveryDescriptions.hasCommunityCoauthors?.options ?? {
                true: 'Sim',
                false: 'Não',
            });
        },
        communicationChannelsOptions () {
            return Vue.markRaw(this.deliveryDescriptions.communicationChannels?.options ?? {});
        },
        documentationTypeOptions () {
            return Vue.markRaw(this.deliveryDescriptions.documentationTypes?.options ?? {});
        },
        executedCommunicationChannels: {
            get () {
                const val = this.proxy.executedCommunicationChannels;
                if (!val) return [];
                if (typeof val === 'string') return JSON.parse(val) ?? [];
                return val;
            },
            set (value) {
                this.proxy.executedCommunicationChannels = value;
            },
        },
        executedDocumentationTypes: {
            get () {
                const val = this.proxy.executedDocumentationTypes;
                if (!val) return [];
                if (typeof val === 'string') return JSON.parse(val) ?? [];
                return val;
            },
            set (value) {
                this.proxy.executedDocumentationTypes = value;
            },
        },
        executedExpectedAccessibilityMeasures: {
            get () {
                const val = this.proxy.executedExpectedAccessibilityMeasures;
                if (!val) return [];
                if (typeof val === 'string') return JSON.parse(val) ?? [];
                return val;
            },
            set (value) {
                this.proxy.executedExpectedAccessibilityMeasures = value;
            },
        },
        executedInnovationTypes: {
            get () {
                const val = this.proxy.executedInnovationTypes;
                if (!val) return [];
                if (typeof val === 'string') return JSON.parse(val) ?? [];
                return val;
            },
            set (value) {
                this.proxy.executedInnovationTypes = value;
            },
        },
        executedRevenueType: {
            get () {
                const val = this.proxy.executedRevenueType;
                if (!val) return [];
                if (typeof val === 'string') return JSON.parse(val) ?? [];
                return val;
            },
            set (value) {
                this.proxy.executedRevenueType = value;
            },
        },
        executedTeamCompositionGender: {
            get () {
                let val = this.proxy.executedTeamCompositionGender;
                if (typeof val === 'string') {
                    try { val = JSON.parse(val); } catch (e) { val = null; }
                }
                return val && typeof val === 'object' ? val : {
                    cisgenderWoman: 0, cisgenderMan: 0,
                    transgenderWoman: 0, transgenderMan: 0,
                    nonBinary: 0, otherGenderIdentity: 0, preferNotToSay: 0
                };
            },
            set (value) {
                this.proxy.executedTeamCompositionGender = value;
            },
        },
        executedTeamCompositionRace: {
            get () {
                let val = this.proxy.executedTeamCompositionRace;
                if (typeof val === 'string') {
                    try { val = JSON.parse(val); } catch (e) { val = null; }
                }
                return val && typeof val === 'object' ? val : {
                    white: 0, black: 0, brown: 0,
                    indigenous: 0, asian: 0, notDeclared: 0
                };
            },
            set (value) {
                this.proxy.executedTeamCompositionRace = value;
            },
        },
        hasExecutedGenderData () {
            const g = this.executedTeamCompositionGender;
            return Object.values(g).some(v => Number(v) > 0);
        },
        hasExecutedDocumentationTypes () {
            return this.executedDocumentationTypes.length > 0;
        },
        hasExecutedExpectedAccessibilityMeasures () {
            return this.executedExpectedAccessibilityMeasures.length > 0;
        },
        hasExecutedInnovationTypes () {
            return this.executedInnovationTypes.length > 0;
        },
        hasExecutedRaceData () {
            const r = this.executedTeamCompositionRace;
            return Object.values(r).some(v => Number(v) > 0);
        },
        hasExecutedRevenueType () {
            return this.executedRevenueType.length > 0;
        },
        executedPaidStaffByRole: {
            get () {
                const val = this.proxy.executedPaidStaffByRole;
                if (!val) return [];
                if (typeof val === 'string') {
                    try { return JSON.parse(val) ?? []; } catch (e) { return []; }
                }
                return Array.isArray(val) ? val : [];
            },
            set (value) {
                this.proxy.executedPaidStaffByRole = value;
            },
        },
        paidStaffRoleOptions () {
            return Vue.markRaw(this.deliveryDescriptions.paidStaffByRole?.options ?? []);
        },
        revenueTypeOptions () {
            return Vue.markRaw(this.deliveryDescriptions.revenueType?.options ?? {});
        },
        segmentDeliveryOptions () {
            return Vue.markRaw(this.deliveryDescriptions.segmentDelivery?.options ?? {});
        },
        accessibilityPlanOptions () {
            return Vue.markRaw(this.deliveryDescriptions.expectedAccessibilityMeasures?.options ?? {});
        },
        innovationTypeOptions () {
            return Vue.markRaw(this.deliveryDescriptions.innovationTypes?.options ?? {});
        },
    },
    methods: {
        convertToCurrency(field) {
            return new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(Number(field));
        },
        normalizeArray(value) {
            if (!value) return [];
            if (typeof value === 'string') {
                try {
                    value = JSON.parse(value);
                } catch (e) {
                    return [];
                }
            }
            return Array.isArray(value) ? value : [];
        },
        normalizeObject(value) {
            if (!value) return null;
            if (typeof value === 'string') {
                try {
                    value = JSON.parse(value);
                } catch (e) {
                    return null;
                }
            }
            return value && typeof value === 'object' && !Array.isArray(value) ? value : null;
        },
        toggleExecutedCommunicationChannel (item) {
            const idx = this.executedCommunicationChannels.indexOf(item);
            const arr = [...this.executedCommunicationChannels];
            if (idx >= 0) arr.splice(idx, 1);
            else arr.push(item);
            this.executedCommunicationChannels = arr;
        },
        toggleExecutedDocumentationType (item) {
            const idx = this.executedDocumentationTypes.indexOf(item);
            const arr = [...this.executedDocumentationTypes];
            if (idx >= 0) arr.splice(idx, 1);
            else arr.push(item);
            this.executedDocumentationTypes = arr;
        },
        toggleExecutedExpectedAccessibilityMeasure (item) {
            const idx = this.executedExpectedAccessibilityMeasures.indexOf(item);
            const arr = [...this.executedExpectedAccessibilityMeasures];
            if (idx >= 0) arr.splice(idx, 1);
            else arr.push(item);
            this.executedExpectedAccessibilityMeasures = arr;
        },
        toggleExecutedInnovationType (item) {
            const idx = this.executedInnovationTypes.indexOf(item);
            const arr = [...this.executedInnovationTypes];
            if (idx >= 0) arr.splice(idx, 1);
            else arr.push(item);
            this.executedInnovationTypes = arr;
        },
        toggleExecutedRevenueType (item) {
            const idx = this.executedRevenueType.indexOf(item);
            const arr = [...this.executedRevenueType];
            if (idx >= 0) arr.splice(idx, 1);
            else arr.push(item);
            this.executedRevenueType = arr;
        },
        calculateGenderTotal (composition) {
            if (!composition) return 0;
            return ['cisgenderWoman','cisgenderMan','transgenderWoman','transgenderMan','nonBinary','otherGenderIdentity','preferNotToSay']
                .reduce((sum, k) => sum + (Number(composition[k]) || 0), 0);
        },
        calculateRaceTotal (composition) {
            if (!composition) return 0;
            return ['white','black','brown','indigenous','asian','notDeclared']
                .reduce((sum, k) => sum + (Number(composition[k]) || 0), 0);
        },
        addExecutedPaidStaffRole () {
            const arr = [...this.executedPaidStaffByRole];
            arr.push({ role: '', count: 0, customRole: '' });
            this.executedPaidStaffByRole = arr;
        },
        removeExecutedPaidStaffRole (index) {
            const arr = [...this.executedPaidStaffByRole];
            arr.splice(index, 1);
            this.executedPaidStaffByRole = arr;
        },
    }
});
