app.component('registration-card', {
    template: $TEMPLATES['registration-card'],
    
    // define os eventos que este componente emite
    emits: ['namesDefined'],

    props: {
        entity: {
            type: Entity,
            required: true
        },
        border: {
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
            switch (this.entity.status) {
                case 0:
                    status = this.text('Rascunho');
                    break;
                case 1:
                    status = this.text('Pendente');
                    break;
                case 2:
                    status = this.text('Inválida');
                    break;
                case 3:
                    status = this.text('Não selecionada');
                    break;
                case 8:
                    status = this.text('Suplente');
                    break;
                case 10:
                    status = this.text('Selecionada');
                    break;
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
