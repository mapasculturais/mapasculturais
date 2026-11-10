app.component('entity-connections-list', {
    template: $TEMPLATES['entity-connections-list'],

    props: {
        type: {
            type: String,
            required: true, // agent | project | space | opportunity
        },
        ids: {
            type: Array,
            default: () => [],
        },
        roleLabel: {
            type: String,
            default: __('ownerRole', 'entity-connections-list'),
        },
        sinceLabel: {
            type: String,
            default: __('ownerSince', 'entity-connections-list'),
        },
        emptyMessage: {
            type: String,
            default: '',
        },
        classes: {
            type: [String, Array, Object],
            required: false,
        },
    },

    computed: {
        normalizedIds() {
            return (this.ids || [])
                .map((item) => {
                    if (item && typeof item === 'object') {
                        return item.id ?? item._id ?? null;
                    }
                    return item;
                })
                .filter((id) => id !== null && id !== undefined && id !== '');
        },

        select() {
            const common = 'id,name,files.avatar,singleUrl,type,terms,createTimestamp,shortDescription';

            if (this.type === 'space') {
                return `${common},endereco,acessibilidade`;
            }

            return common;
        },

        opportunities() {
            if (this.type !== 'opportunity') {
                return [];
            }

            const list = $MAPAS.opportunityList?.opportunity || [];
            return list.map((element) => {
                const entity = new Entity('opportunity', element.id);
                entity.populate(element);
                return entity;
            });
        },

        hasItems() {
            if (this.type === 'opportunity') {
                return this.opportunities.length > 0;
            }

            return this.normalizedIds.length > 0;
        },

        resolvedEmptyMessage() {
            if (this.emptyMessage) {
                return this.emptyMessage;
            }

            const map = {
                agent: __('emptyAgent', 'entity-connections-list'),
                project: __('emptyProject', 'entity-connections-list'),
                space: __('emptySpace', 'entity-connections-list'),
                opportunity: __('emptyOpportunity', 'entity-connections-list'),
            };

            return map[this.type] || '';
        },

        typeLabel() {
            const map = {
                agent: __('typeOrganization', 'entity-connections-list'),
                project: __('typeProject', 'entity-connections-list'),
                space: __('typeSpace', 'entity-connections-list'),
                opportunity: __('typeOpportunity', 'entity-connections-list'),
            };

            return map[this.type] || '';
        },

        areasLabel() {
            if (this.type === 'project') {
                return __('tags', 'entity-connections-list');
            }

            if (this.type === 'opportunity') {
                return __('interestAreas', 'entity-connections-list');
            }

            return __('areas', 'entity-connections-list');
        },
    },

    methods: {
        areas(entity) {
            const terms = entity?.terms?.area || entity?.terms?.tag || [];
            return Array.isArray(terms) ? terms : [];
        },

        areasText(entity) {
            return this.areas(entity).map((term) => String(term).toUpperCase()).join(', ');
        },

        formatDate(value) {
            if (!value) {
                return '';
            }

            if (typeof value === 'string' && /^\d{2}\/\d{2}\/\d{4}$/.test(value)) {
                return value;
            }

            let mcDate = null;
            if (value instanceof McDate) {
                mcDate = value;
            } else if (typeof value === 'string') {
                mcDate = new McDate(value);
            } else if (typeof value?.date === 'string' && value.date) {
                mcDate = new McDate(value.date);
            }

            return mcDate ? mcDate.date('2-digit year') : '';
        },

        opportunityStatus(opp) {
            if (!opp?.registrationTo) {
                return '';
            }

            if (opp.registrationTo.isPast && opp.registrationTo.isPast()) {
                return __('registrationClosed', 'entity-connections-list');
            }

            if (opp.registrationFrom?.isFuture && opp.registrationFrom.isFuture()) {
                return __('registrationFuture', 'entity-connections-list');
            }

            return __('registrationOpen', 'entity-connections-list');
        },
    },
});
