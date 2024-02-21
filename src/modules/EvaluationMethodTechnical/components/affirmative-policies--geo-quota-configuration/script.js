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

app.component('affirmative-policies--geo-quota-configuration', {
    template: $TEMPLATES['affirmative-policies--geo-quota-configuration'],
    
    // define os eventos que este componente emite
    emits: ['namesDefined'],

    props: {

    },
    
    setup(props, { slots }) {
        const hasSlot = name => !!slots[name];
        // os textos estão localizados no arquivo texts.php deste componente 
        const text = Utils.getTexts('affirmative-policies--geo-quota-configuration')
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
            isActive: false,
            geoQuota: {
                geoDivision: '',
                distribution: {

                }
            }
        }
    },

    computed: {
        divisions() {
            return $MAPAS.geoQuotaConfiguration.geoDivisions;
        }
    },
    
    methods: {
        open() {
            this.isActive = true;
        },

        close() {
            this.isActive = false;
        },

        setDivision(option) {
            this.geoQuota.geoDivision = option.value;
        },
    },
});
