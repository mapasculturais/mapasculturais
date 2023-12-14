app.component('notification-modal' , {
    template: $TEMPLATES['notification-modal'],

    setup() {
        // os textos estão localizados no arquivo texts.php deste componente
        const text = Utils.getTexts('notification-modal')
        return { text }
    },
    
    created() {
        globalThis.addEventListener('afterFetch', (e) => {
            const header = e.detail.headers.get('MC-notifications-count');
            if(header !== null){
                this.notificationsCount = header;
            }
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
        viewport: {
            type: String,
            default: 'desktop'
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
