app.component('notification-modal' , {
    template: $TEMPLATES['notification-modal'],

    setup() {
        // os textos estão localizados no arquivo texts.php deste componente
        const text = Utils.getTexts('notification-modal')
        return { text }
    },
    
    created() {
        globalThis.addEventListener('afterFetch', (e) => {
            this.notificationsCount = e.detail.headers.get('MC-notifications-count');
        });
    },

    data() {
        return {
            entity: null,
            fields: [],
            notificationsCount: $MAPAS.notificationsCount || 0,
            modalTitle: this.text('notificacao')
        }
        
    },

    props: {
        typeStyle: {
            type: String,
            default: 'normal'
        }
    },

    computed: {
        entities(){
            const api = new API('notification','default');
            return api.lists.fetch('notification-list','default')
        }
    },
    
    methods: {

    },
});
