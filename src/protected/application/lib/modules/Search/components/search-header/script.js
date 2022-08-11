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

app.component('search-header', {
    template: $TEMPLATES['search-header'],
    
    // define os eventos que este componente emite
    emits: ['namesDefined'],

    setup() { 
        // os textos estão localizados no arquivo texts.php deste componente 
        const text = Utils.getTexts('search-header')
        return { text }
    },

    beforeCreate() { },
    created() { },

    beforeMount() { },
    mounted() { },

    beforeUpdate() { },
    updated() { },

    beforeUnmount() {},
    unmounted() {},

    props: {
       
        type: {
            type: String,
            required: true,
        }
    },

    data() {
        return {
            entityName: __(this.type, 'search-header'),
        }
    },

    computed: {
        compareDisplayName() {
            return this.entity.name == this.displayName;
        },

        compareFullname() {
            return this.entity.nomeCompleto == this.fullname;
        }
    },
    
    methods: {
        defineNames () {
            this.entity.name = this.displayName;
            this.entity.nomeCompleto = this.fullname;

            // emite o evento enviando o data
            this.$emit('namesDefined', this.entity);
        }
    },
});
