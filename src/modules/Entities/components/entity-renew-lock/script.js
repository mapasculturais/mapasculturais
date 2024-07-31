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
            token: $MAPAS.lockToken ?? null,
            message: '',
        }
    },

    methods: {
        renewLock() {
            // const messages = useMessages();

            this.entity.POST('renewLock', {
                data: {token: this.token}, callback: data => {}
            }).catch((data) => {
                if (data.error) {
                    // messages.error(data.data);
                    this.$refs.modalBlock.open();
                }
            });
        },

        unlock() {
            document.location = this.entity.getUrl('unlock');
        },

        exit() {
            document.location = this.entity.getUrl('single');
        }
    },

    mounted() {
        setInterval(() => {
            this.renewLock();
        }, 
        $MAPAS.config['entity-renew-lock']['renewInterval'] * 1000);
    }
    
});
