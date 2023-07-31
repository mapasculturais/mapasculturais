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
            subsites: $MAPAS.subsites,
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
            this.instance.userId = this.user.id;

            this.instance.save().then((response) => {
                this.user.roles.push(this.instance);
                modal.close();
            })
            .catch((error) => {
                this.__processing = false;
            });;
        },
        createInstance(){
            this.instance =  new Entity('role');
        }
    },
});
