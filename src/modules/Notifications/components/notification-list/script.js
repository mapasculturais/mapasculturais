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


app.component('notification-list', {
    template: $TEMPLATES['notification-list'],

    setup() { 
        // os textos estão localizados no arquivo texts.php deste componente 
        const text = Utils.getTexts('notification-list')
        return { text }
    },

    beforeCreate() { },
    created() { 
        this.API = new API('notification');
    },

    beforeMount() { },
    mounted() { },

    beforeUpdate() { },
    updated() { },

    beforeUnmount() {},
    unmounted() {},

    props: {
        query:{
            type:Object,
            default:{
                '@select':'*,request.{requesterUser.profile.files.avatar}',
                'user':'eq(@me)',
                '@order':'createTimestamp DESC'
            }
        },
        styleCss: {
            type: String,
            default: 'card'
        }
    },

    data() {
        const global = useGlobalState();
        return {
            currentUserId: global.auth.user?.id
        }
    },

    computed: {

    },
    
    methods: {
        hasAvatar(entity) {
            return !!entity.request?.requesterUser?.profile?.files?.avatar;
        },

        avatarUrl(entity) {
            if (this.hasAvatar(entity)) {
                return entity.request?.requesterUser?.profile?.files?.avatar?.transformations?.avatarSmall?.url ||
                       entity.request?.requesterUser?.profile?.files?.avatar?.url;
            }
        },

        async approve(notification) {
            const url = this.API.createUrl('approve',[notification.id]);
            const request = await this.API.POST(url);
            if(request) {
                const messages = useMessages();
                messages.success(this.text('notificacao_aprovada'));
            }
        },
        async reject(notification) {
            const url = this.API.createUrl('reject',[notification.id]);
            const request = await this.API.POST(url)
            if(request) {
                const messages = useMessages();
                messages.success(this.text('notificacao_recusada'));
                notification.removeFromLists();
            }
        },
        async cancel (notification) {
            const url = this.API.createUrl('reject',[notification.id]);
            const request = await this.API.POST(url)
            if(request) {
                const messages = useMessages();
                messages.success(this.text('notificacao_cancelada'));
                notification.removeFromLists();
            }
        },
        async ok (notification) {
            notification.disableMessages();
            notification.delete();
            if(notification) {
                notification.removeFromLists();
            }
        }
    },
});