app.component('panel--card-user', {
    template: $TEMPLATES['panel--card-user'],
    emits: [],

    setup(props, { slots }) {
        const hasSlot = name => !!slots[name]
        return { hasSlot }
    },

    created() {

    },

    data() {
        let username = `${this.entity.profile?.name} <${this.entity.email}>`;
        return {
            username
        };
    },

    computed: {
        roles() {
            if(!this.entity.roles) {
                this.entity.roles = [];
            }
            return this.entity.roles;
        }
    },

    props: {
        entity: {
            type: Entity,
            required: true
        }
    },
    
    methods: {
        async deleteRole(role) {
            try {
                await role.delete(true);
                const index = this.roles.indexOf(role);
                this.roles.splice(index,1);
            } catch(e) {
                console.error(e);
            }
        }
    },
});
