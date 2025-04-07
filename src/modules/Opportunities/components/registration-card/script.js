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
        },
        list:{
            type: Object,
            required: false,
            default: null
        }
    },

    setup() { 
        // os textos estão localizados no arquivo texts.php deste componente 
        const text = Utils.getTexts('registration-card');
        return { text }
    },
    data() {
        return{
            faixa: $MAPAS.EntitiesDescription.opportunity.registrationRanges,
            proponent: $MAPAS.EntitiesDescription.opportunity.registrationProponentTypes.optionsOrder,
        }
    },
    computed: {
        status() {
            let status = '';
            if (this.entity.status == 0) {
                status = this.text('não enviada');
            } else {
                status = this.text('enviada');
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
        verifyStatus() {
            if(this.entity.status == 0){
                return true;
            }
            return false;
        },
        
        async deleteRegistration() {
            const messages = useMessages();

            try {
                this.entity.disableMessages();
                await this.entity.delete(true);
                messages.success(this.text('inscrição removida'));
            } catch (e) {
                messages.success(this.text('erro ao remover'));
            }
        }
    },
});
