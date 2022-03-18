app.component('user-management--delete-role-modal', {
    template: $TEMPLATES['user-management--delete-role-modal'],
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
        },
        role: {
            type: Object,
            required:true
        }
    },
    
    methods: {
        deleteRole(){
            this.instance.delete();
        },
        createInstance(){
            this.instance =  new Entity('role');
            this.instance.id = this.role.id;
            this.instance.user = this.user;
        }
    },
});
