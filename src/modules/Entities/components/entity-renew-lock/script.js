app.component('entity-renew-lock', {
    template: $TEMPLATES['entity-renew-lock'],

    props: {
        entity: {
            type: Entity,
            required: true,
        }
    },

    setup() { 
        // os textos estão localizados no arquivo texts.php deste componente 
        const text = Utils.getTexts('entity')
        return { text }
    },

    created() {
        const self = this;
        globalThis.addEventListener('afterFetch', (e) => {
            const header = e.detail.headers.get('Error-Code');
            if(header === '1'){
                self.$refs.modalBlock.open();
            }
        });
    },

    data() {
        return {
            token: $MAPAS.lockToken ?? null,
            message: '',
            usesLock: $MAPAS.config['entity-renew-lock']['usesLock'],
            locked: false
        }
    },

    methods: {
        renewLock() {
            this.entity.POST('renewLock', {
                data: {token: this.token}, callback: data => {}
            }).catch((data) => {
                if (data.error) {
                    // messages.error(data.data);
                    this.$refs.modalBlock.open();
                }
            });
        },

        async unlock(modal) {
            const messages = useMessages();

            try{
                await this.entity.POST('unlock', {
                  data: { token: this.token }, callback: data => { }
                });
                modal.close();
                messages.success(this.text('você assumiu o controle da entidade'));
            } catch(e) {
                messages.error(this.text('não foi possível desbloquear a entidade'));
            }
        },

        exit() {
            document.location = this.entity.getUrl('single');
        },

        setCookie() {
            if(this.token) {
                Utils.cookies.set('lockToken', this.token);
            }
        }
    },

    mounted() {
        if(this.usesLock) {
            this.setCookie();

            setInterval(() => {
                if (this.token) {
                    this.renewLock();
                }
            },
            $MAPAS.config['entity-renew-lock']['renewInterval'] * 1000);
        }
    }

});
