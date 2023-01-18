app.component('user-management--delete', {
    template: $TEMPLATES['user-management--delete'],
    emits: ['delete'],

    setup(props, { slots }) {
        const hasSlot = name => !!slots[name]
        return { hasSlot }
    },

    created() {

    },

    data() {
        return {
        }
    },

    props: {
        user: {
            type: Entity,
            required: true
        }
    },
    
    methods: {
        delete (modal) {
            this.instance.userId = this.user.id;

            this.instance.delete().then((response) => {
                modal.close();
            })
            .catch((error) => {
                this.__processing = false;
            });
        }
    },
});
