app.component('entity-social-media', {
    template: $TEMPLATES['entity-social-media'],
    emits: [],

    setup(props, { slots }) {
        const hasSlot = name => !!slots[name]
        return { hasSlot }
    },

    created() {
        if (this.entity.instagram || this.entity.twitter || this.entity.linkedin || this.entity.facebook || this.entity.youtube || this.entity.spotify || this.entity.pinterest) {
            this.show = true;
        }
    },

    data() {
        return {
            show: false,
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
        }
    },
    
    methods: {
        doSomething () {

        }
    },
});
