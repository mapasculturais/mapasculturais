app.component('app-card-content', {
    template: $TEMPLATES['app-card-content'],
    emits: [''],

    setup() {
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
                    let keyLenght = this.entity.privateKey.length;
                    let hiddenKey = '';
                    do {
                        hiddenKey+='*';
                        keyLenght--;
                    } while (keyLenght > 0)
                    return hiddenKey;
                }
            }
        },
    },

    methods: {
        toggleKey() {
            this.showPrivateKey = !this.showPrivateKey;
        },
        copyDescription() {
            let description = document.querySelector(".spanComOTexto");
            if (description) {
                navigator.clipboard.writeText(description.innerHTML);
            }
        },
    },
});
