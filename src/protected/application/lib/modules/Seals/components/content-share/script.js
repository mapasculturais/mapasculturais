app.component('content-share' , {
    template: $TEMPLATES['content-share'],

    setup() {
        // os textos estão localizados no arquivo texts.php deste componente
        const text = Utils.getTexts('content-share')
        return { text }
    },

    // created() {
    //     globalThis.addEventListener('afterFetch', (e) => {
    //         this.notificationsCount = e.detail.headers.get('MC-notifications-count');
    //     });
    // },

    data() {
        return {
            entity: null,
            fields: [],
            modalTitle: this.text('title')
        }

    },

    // computed: {
    //     entities(){
    //         const api = new API('notification','default');
    //         return api.lists.fetch('notification-list','default')
    //     }
    // },

    methods: {

    },
});
