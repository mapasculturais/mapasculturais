app.component('entity-renew-lock', {
    template: $TEMPLATES['entity-renew-lock'],

    props: {
        entity: {
            type: Entity,
            required: true,
        }
    },

    data() {
        return {
            token: $MAPAS.lockToken ?? null
        }
    },

    methods: {
        renewLock() {
            const messages = useMessages();

            this.entity.POST('renew', {
                data: {token: this.token}, callback: data => {}
            }).catch((data) => {
                messages.error(data.data);
            });
        }
    },

    mounted() {
        setInterval(() => {
            this.renewLock();
        }, 45000);
    }
    
});
