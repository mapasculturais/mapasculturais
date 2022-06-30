app.component('system-roles--create-modal', {
    template: $TEMPLATES['system-roles--create-modal'],
    emits: ['created'],

    setup(props, { slots }) {
        
        const hasSlot = name => !!slots[name]
        return { hasSlot }
    },

    created() {

    },

    data() {
        return {
            instance: this.createSystemRoleInstance(),
            permissions: $MAPAS.EntityPermissionsList || []
        }
    },

    props: {
        list: String
    },
    
    methods: {
        createSystemRoleInstance() {
            const instance = new Entity('system-role');
            instance.permissions = [];
            return instance;    
        },

        create (modal) {
            const lists = useEntitiesLists();
            this.instance.save().then((response) => {
                const list = lists.fetch(this.list);
                list.push(response);
                modal.close();
            });
        },

        cancel (modal) {
            modal.close();
        },

        resetInstance() {
            this.instance = this.createSystemRoleInstance();
        }
    },
});
