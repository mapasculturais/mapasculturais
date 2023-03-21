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

app.component('mc-notification', {
    template: $TEMPLATES['mc-notification'],

    props: {
        type: {
            type: String,
            required: true
        },

        message: {
            type: String
        },

    },
    
    setup(props, { slots }) {
        const hasSlot = name => !!slots[name];
        // os textos estão localizados no arquivo texts.php deste componente 
        const text = Utils.getTexts('mc-notification');
        return { text, hasSlot };
    },

    computed: {
        icon () {
          switch (this.type) {
              case 'success' : return 'circle-checked';
              case 'error' : return 'error';
              case 'info' : return 'info';
              default: return '';
          }
        },
        msg () {
            return this.message;
        },
        classes () {
            return `mc-notification mc-notification-${this.type}`;
        }
    }
});
