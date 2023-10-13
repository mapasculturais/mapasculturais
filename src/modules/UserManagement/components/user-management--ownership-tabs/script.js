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

app.component('user-management--ownership-tabs', {
    template: $TEMPLATES['user-management--ownership-tabs'],
    
    // define os eventos que este componente emite
    emits: ['namesDefined'],

    setup() { 
        // os textos estão localizados no arquivo texts.php deste componente 
        const text = Utils.getTexts('user-management--ownership-tabs')
        return { text }
    },

    props: {
        user: {
            type: Entity,
            required: true
        },

        type: {
            type: String,
            required: true
        },

        select: {
            type: String,
            default: 'id,name,status,subsite.name,files'
        },
    },
    computed: {
        newSelect() {
            if(this.type=='registration') {
                return this.select+',owner.name,number,createTimestamp,opportunity.{files.avatar,name}';
            } else {
                return this.select;
            }
        },

    },
});
