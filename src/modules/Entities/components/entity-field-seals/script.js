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

    data () {
        return {
            fadeTimeout: null,
        }
    },
    
    computed: {
        seals () {
            const sealIds = this.entity.__lockedFieldSeals?.[this.prop] ?? [];
            return sealIds.map((sealId) => {
                return this.entity.seals?.find((seal) => seal.sealId == sealId);
            }).filter(Boolean);
        },
    },

    watch: {
        seals: {
            handler () {
                if (this.seals.length === 0) {
                    this.setSeal(null);
                } else if (this.seals.length === 1) {
                    this.setSeal(this.seals[0]);
                }
                this.$emit('count', this.seals.length);
            },
            immediate: true,
        },
    },

    unmounted () {
        this.removeTimeout();
    },

    methods: {
        formatDate (date) {
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

        removeTimeout () {
            if (this.fadeTimeout) {
                globalThis.clearTimeout(this.fadeTimeout);
                this.fadeTimeout = null;
            }
        },

        setSeal (seal) {
            this.removeTimeout();
            if (seal) {
                const text = this.text('validadoPor', { authority: seal.name, date: this.formatDate(seal.createTimestamp.date) })
                this.$emit('select', { seal, text });
            } else if (this.seals.length > 1) {
                this.$emit('select', { seal: null, text: null });
            }
        },

        setSealTouch (seal) {
            if (this.$media('(pointer: coarse)')) {
                this.setSeal(seal);
                this.fadeTimeout = globalThis.setTimeout(() => {
                    this.setSeal(null);
                }, 10_000);
            }
        }
    },
});
