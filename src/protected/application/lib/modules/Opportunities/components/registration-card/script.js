app.component('registration-card', {
    template: $TEMPLATES['registration-card'],
    
    // define os eventos que este componente emite
    emits: ['namesDefined'],

    props: {
        entity: {
            type: Entity,
            required: true
        },
    },

    setup() { 
        // os textos estão localizados no arquivo texts.php deste componente 
        const text = Utils.getTexts('registration-card')
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

    data() {
        return {
        }
    },

    computed: {
        
    },
    
    methods: { 
        registerDate(date) {
            return date.day('2-digit')+'/'+date.month('2-digit')+'/'+date.year('numeric');
        },
        registerHour(date) {
            return date.hour('2-digit')+'h';
        },
    },
});
