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
        verifyStatus() {
            if(this.entity.status == 0){
                return true;
            }
            return false;
        },
        deleteRegistration() {
            const messages = useMessages();
            let pos = this.list.indexOf(this.entity);
            api = new API('registration');
            let url = api.createUrl('deleteRegistration', {id: this.entity.id});
            var args = {};
            api.POST(url, args).then(res => res.json()).then(data => {
                if(data){
                    this.list.splice(pos,1);
                    messages.success(this.text('Inscrição deletada com sucesso'));
                }else{
                    messages.error(this.text('Erro ao deletada a inscrição'));
                }
            });
        }
    },
});
