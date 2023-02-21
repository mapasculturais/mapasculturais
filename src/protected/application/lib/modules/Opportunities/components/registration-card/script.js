app.component('registration-card', {
    template: $TEMPLATES['registration-card'],
    
    // define os eventos que este componente emite
    emits: ['namesDefined'],

    props: {
        entity: {
            type: Entity,
            required: true
        },
        hasBorder: {
            type: Boolean,
            default: false
        },
        pictureCard: {
            type: Boolean,
            default: false
        }
    },

    setup() { 
        // os textos estão localizados no arquivo texts.php deste componente 
        const text = Utils.getTexts('registration-card');
        return { text }
    },

    computed: {
        status() {
            let status = '';
            if (this.entity.status == 0) {
                status = this.text('Não enviada');
            } else {
                status = this.text('Enviada');
            }
            return status;
        },
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
