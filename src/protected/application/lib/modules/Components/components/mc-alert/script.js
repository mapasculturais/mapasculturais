/**
 * Vue Lifecycle
 * 1. setup
 * 2. beforeCreate
 * 3. created
 * 4. beforeMount
 * 5. mounted
 * 
 * // sempre que há modificação nos dados
 *  - beforeUpdate
 *  - updated
 * 
 * 6. beforeUnmount
 * 7. unmounted                  
 */

app.component('mc-alert', {
    template: $TEMPLATES['mc-alert'],
    
    // define os eventos que este componente emite
    emits: ['namesDefined'],

    props: {
        type: {
            type: String,
            required: true
        },

    },
    
    setup(props, { slots }) {
        const hasSlot = name => !!slots[name];
        // os textos estão localizados no arquivo texts.php deste componente 
        const text = Utils.getTexts('mc-alert')
        return { text, hasSlot }
    },

    beforeCreate() { },
    created() { },

    beforeMount() { },
    mounted() { },

    beforeUpdate() { },
    updated() { },

    beforeUnmount() {},
    unmounted() {},

    data() {
        return {
            showAlert: true,
        }
    },

    computed: {
    },
    
    methods: {
        open() {
            if (!this.showAlert) {
                this.showAlert = true;
            }
        },
        close() {
            if (this.showAlert) {
                this.showAlert = false;
            }
        },
        toogle() {
            if (!this.showAlert) {
                this.open();
            } else {
                this.close();
            }
        }
    },
});
