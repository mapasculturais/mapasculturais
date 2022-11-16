app.component('entity-social-media', {
    template: $TEMPLATES['entity-social-media'],
    emits: [],

    setup(props, { slots }) {
        const hasSlot = name => !!slots[name]
        return { hasSlot }
    },

    data() {
        return {
            show: !!(this.entity.instagram || this.entity.twitter || this.entity.linkedin || this.entity.facebook || this.entity.youtube || this.entity.spotify || this.entity.pinterest),
        }
    },

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
    },
    
    methods: {
        doSomething () {

        }
    },
});
