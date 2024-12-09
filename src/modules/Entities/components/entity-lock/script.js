app.component('entity-lock', {
    template: $TEMPLATES['entity-lock'],

    props: {
        entity: {
            type: Entity,
            required: true,
        }
    },

    data() {
        return {
            entityLock: $MAPAS.entityLock ?? null,
            prefix: this.text(this.entity.__objectType),
        }
    },

    setup() {
        const text = Utils.getTexts('entity-lock')
        return { text }
    },

    methods: {
        formatDate(value) {
            if(value) {
                let date = new McDate(value);
                return `${date.date('numeric year')} ${this.text('as')} ${date.time('numeric')} `;
            }
        },

        unlock() {
            document.location = this.entity.getUrl('unlock');
        }
    },
});
