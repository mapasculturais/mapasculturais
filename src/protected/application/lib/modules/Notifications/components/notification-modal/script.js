app.component('notification-modal' , {
    template: $TEMPLATES['notification-modal'],
    emits: ['create'],

    setup() {
        // os textos estão localizados no arquivo texts.php deste componente
        const text = Utils.getTexts('notification-modal')
        return { text }
    },
    
    created() {
        globalThis.addEventListener('afterFetch', (e) => {
            this.notificationsCount = e.detail.headers.get('MC-notifications-count');
        });
        const mql = globalThis.matchMedia(`(${this.mediaQuery})`);
        console.log(mql);
        console.log(this.mediaQuery);
        this.show = mql.matches;
        mql.addEventListener("change", (event) => {
            this.show = event.matches;
            
        });
    },

    data() {
        return {
            entity: null,
            fields: [],
            notificationsCount: $MAPAS.notificationsCount || 0,
            modalTitle: 'Notificações',
            show: null
        }
        
    },

    props: {
        mediaQuery:{
            type:String,
            required:true
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
