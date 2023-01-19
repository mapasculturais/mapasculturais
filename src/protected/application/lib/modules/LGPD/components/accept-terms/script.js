app.component('accept-terms', {
    template: $TEMPLATES['accept-terms'],
    emits: [],
   
    data() {
        const terms = $MAPAS.config.LGPD;
        const accepteds = $MAPAS.hashAccepteds;
        const step = this.getStep();
        var user = {};

        return {
            terms, accepteds, step, user
        };
    },
    async created(){
        let api = new API('user');
        let userId = $MAPAS.userId;
        let query = {
            '@select': '*',
            'id': `EQ(`+userId+`)`
        };

        this.user = await api.find(query);
    },

    props: {},
    methods: {

        formatDate(timestamp) {
            let date = new McDate(new Date(timestamp * 1000));
            return date.date('numeric year') + ' - ' + date.time('numeric');
        },
        acceptTerm(slug, hash) {
            let url = Utils.createUrl('lgpd', 'accept');
            let api = new API();
            api.POST(url, [slug]).then(res => res.json()).then(data => {
                window.location.href = data.redirect;
                this.accepteds.push(hash);
            })
        },
        showButton(hash) {
            if (this.accepteds.includes(hash)) {
                return false;
            }
            return true;

        },
        showIconAccepted(hash) {
            if (this.accepteds.includes(hash)) {
                return "circle-checked";
            }
        },
        getStep(){
            if(window.location.href.match(/[a-zA-z./0-9]?#([a-zA-z]{1,61})[0-9]?/)){
                return window.location.href.match(/[a-zA-z./0-9]?#([a-zA-z]{1,61})[0-9]?/)[1];
            }
        }
    },
});
