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

app.component('theme-logo', {
    template: $TEMPLATES['theme-logo'],

    setup() {
        // os textos estão localizados no arquivo texts.php deste componente 
        const text = Utils.getTexts('theme-logo')
        return { text }
    },

    props: {
        href: {
            type: String,
            default: null
        },
        primaryBackground: {
            type: String,
            default: '#4FC9E0',
            
        },
        secondaryBackground: {
            type: String,
            default: '#0074C1',
        },


    },

    data() {
        return {
            title: $MAPAS.config.logo.title,
            subtitle: $MAPAS.config.logo.subtitle,
            color: $MAPAS.config.logo.color
        }
    },
});
