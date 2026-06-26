app.component('entity-field-seals', {
    template: $TEMPLATES['entity-field-seals'],

    props: {
        entity: {
            type: Entity,
            required: true
        },
        prop: {
            type: String,
            required: true
        },
    },

    emits: ['count', 'select'],

    setup () {
        const text = Utils.getTexts('entity-field-seals');
        return { text };
    },
    
    computed: {
        seals () {
            return this.entity.$fieldSealStatuses?.[this.prop] ?? this.entity.$lockedFieldSeals?.[this.prop] ?? [];
        },
    },

    watch: {
        seals: {
            handler () {
                if (this.seals.length === 0) {
                    this.setSeal(null);
                } else {
                    this.setSeal(this.seals[0]);
                }
                this.$emit('count', this.seals.length);
            },
            immediate: true,
        },
    },

    methods: {
        formatDate (date) {
            if (!date) {
                return '';
            }

            let mcDate;
            if (date instanceof McDate) {
                mcDate = date;
            } else if (typeof date === 'string') {
                mcDate = new McDate(date);
            } else {
                mcDate = new McDate(date.date);
            }
            return mcDate.date('2-digit year');
        },

        formatText (seal) {
            if (seal.fieldStatus === 'about_to_expire') {
                return this.text('prestesAExpirar', {
                    authority: seal.name,
                    date: seal.expiryDate || '',
                });
            }

            if (seal.fieldStatus === 'expired') {
                return this.text('expirado', {
                    authority: seal.name,
                    date: seal.expiryDate || '',
                });
            }

            return this.text('validadoPor', {
                authority: seal.name,
                date: this.formatDate(seal.createTimestamp.date),
            })
        },

        sealStatusClass (seal) {
            return {
                'entity-field-seal--valid': ['valid', 'no_expiration'].includes(seal.fieldStatus),
                'entity-field-seal--about-to-expire': seal.fieldStatus === 'about_to_expire',
                'entity-field-seal--expired': seal.fieldStatus === 'expired',
                'entity-field-seal--invalidator': seal.isInvalidator,
            };
        },

        setSeal (seal) {
            if (seal) {
                const text = this.formatText(seal);
                this.$emit('select', { seal, text });
            } else if (this.seals.length > 1) {
                this.$emit('select', { seal: null, text: null });
            }
        },

        setSealTouch (seal) {
            if (this.$media('(pointer: coarse)')) {
                this.setSeal(seal);
            }
        }
    },
});
