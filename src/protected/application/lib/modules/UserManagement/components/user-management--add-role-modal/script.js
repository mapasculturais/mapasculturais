app.component('user-management--add-role-modal', {
    template: $TEMPLATES['user-management--add-role-modal'],
    emits: ['created'],

    setup(props, { slots }) {
        const hasSlot = name => !!slots[name]
        return { hasSlot }
    },

    created() {

    },

    data() {
        return {
            instance: null,
        }
    },

    props: {
        user: {
            type: Entity,
            required: true
        }
    },
    
    methods: {
        create (modal) {
         this.instance.save();
        },
        createInstance(){
            this.instance =  new Entity('role');
            this.instance.user = this.user;
        }
    },
});
