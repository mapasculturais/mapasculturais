app.component('entity-seals-list', {
    template: $TEMPLATES['entity-seals-list'],

    props: {
        entity: {
            type: Entity,
            required: true
        },
        editable: {
            type: Boolean,
            default: false
        },
        classes: {
            type: [String, Array, Object],
            required: false
        },
        emptyMessage: {
            type: String,
            default: __('empty', 'entity-seals-list'),
        },
    },

    computed: {
        query() {
            const seals = this.entity.seals || [];
            const ids = seals.map((item) => item.sealId).filter(Boolean).join(',');
            return ids ? { id: `!IN(${ids})` } : {};
        },

        sortedSeals() {
            const seals = [...(this.entity.seals || [])];

            return seals.sort((a, b) => {
                const aExpired = this.isExpired(a) ? 1 : 0;
                const bExpired = this.isExpired(b) ? 1 : 0;
                return aExpired - bExpired;
            });
        },

        hasSeals() {
            return (this.entity.seals || []).length > 0;
        },
    },

    methods: {
        addSeal(seal) {
            this.entity.createSealRelation(seal);
        },

        removeSeal(seal) {
            this.entity.removeSealRelation(seal);
        },

        isExpired(seal) {
            if (!seal) {
                return false;
            }

            if (seal.computedStatus === 'invalid') {
                return true;
            }

            const period = Number(seal.validPeriod || 0);
            if (period <= 0) {
                return false;
            }

            const date = this.parseDate(seal.validateDate);
            if (!date) {
                return false;
            }

            const today = new Date();
            today.setHours(0, 0, 0, 0);
            return date < today;
        },

        parseDate(value) {
            if (!value) {
                return null;
            }

            if (value instanceof Date) {
                return value;
            }

            if (typeof value === 'string') {
                const brMatch = value.match(/^(\d{2})\/(\d{2})\/(\d{4})$/);
                if (brMatch) {
                    return new Date(Number(brMatch[3]), Number(brMatch[2]) - 1, Number(brMatch[1]));
                }

                const mcDate = new McDate(value);
                return mcDate?._date || null;
            }

            if (typeof value?.date === 'string' && value.date) {
                const mcDate = new McDate(value.date);
                return mcDate?._date || null;
            }

            return null;
        },

        formatDate(value) {
            if (!value) {
                return '';
            }

            if (typeof value === 'string' && /^\d{2}\/\d{2}\/\d{4}$/.test(value)) {
                return value;
            }

            const date = this.parseDate(value);
            if (!date) {
                return '';
            }

            return new McDate(date).date('2-digit year');
        },

        formatValidity(seal) {
            const period = Number(seal?.validPeriod || 0);
            if (period <= 0) {
                return __('doesNotExpire', 'entity-seals-list');
            }

            const until = this.formatDate(seal.validateDate);
            return __('monthsUntil', 'entity-seals-list')
                .replace('{months}', String(period))
                .replace('{date}', until || '00/00/0000');
        },

        sealHref(seal) {
            return seal?.sealUrl || seal?.singleUrl || '#';
        },

        removeConfirmMessage(seal) {
            return __('removeConfirm', 'entity-seals-list').replace('{name}', seal?.name || '');
        },
    },
});
