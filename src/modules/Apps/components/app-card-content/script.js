app.component('app-card-content', {
    template: $TEMPLATES['app-card-content'],
    emits: [''],

    setup() {
        const messages = useMessages();
        const text = Utils.getTexts('app-card-content');
        return { text }

    },

    props: {
        entity: {
            type: Entity,
            required: true
        },
    },

    data() {

        return {
            showPrivateKey: false,
        }
    },

    computed: {
        privateKey() {
            if (this.entity.privateKey) {
                if (this.showPrivateKey) {
                    return this.entity.privateKey;
                } else {
                    let keyLength = this.entity.privateKey.length;
                    let hiddenKey = '';
                    do {
                        hiddenKey += '*';
                        keyLength--;

                    } while (keyLength > 0)
                    return hiddenKey;
                }
            }
        },
    },

    methods: {
        toggleKey() {
            this.showPrivateKey = !this.showPrivateKey;
        },
        copyPublicKey() {
            messages.success(__('message','app-card-content'));


            if (this.entity.publicKey) {
                navigator.clipboard.writeText(this.entity.publicKey);
            }
        },
        copyPrivateKey() {
            messages.success(__('message','app-card-content'));
            if (this.entity.privateKey) {
                navigator.clipboard.writeText(this.entity.privateKey);
            }
        }
    },
});
