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
            return Vue.markRaw($DESCRIPTIONS.delivery.accessibilityMeasures.options);
        },
        audienceOptions () {
            return Vue.markRaw($DESCRIPTIONS.delivery.priorityAudience.options);
        },
        availabilityOptions () {
            return Vue.markRaw($DESCRIPTIONS.delivery.availabilityType.options);
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
            return Vue.markRaw($DESCRIPTIONS.delivery.artChainLink?.options ?? []);
        },
        communicationChannelsOptions () {
            return Vue.markRaw($DESCRIPTIONS.delivery.communicationChannels?.options ?? {});
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
        hasExecutedRaceData () {
            const r = this.executedTeamCompositionRace;
            return Object.values(r).some(v => Number(v) > 0);
        },
    },
    methods: {
        convertToCurrency(field) {
            return new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(Number(field));
        },
        toggleExecutedCommunicationChannel (item) {
            const idx = this.executedCommunicationChannels.indexOf(item);
            const arr = [...this.executedCommunicationChannels];
            if (idx >= 0) arr.splice(idx, 1);
            else arr.push(item);
            this.executedCommunicationChannels = arr;
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
    }
});
