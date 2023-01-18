app.component('accept-terms', {
    template: $TEMPLATES['accept-terms'],
    emits: [],
   
    data() {
        const terms = $MAPAS.config.LGPD;
        let accepteds = $MAPAS.hashAccepteds;
        return {
            terms, accepteds,
        };
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
            
            api.POST(url, [slug])
            this.accepteds.push(hash);
            window.location.href = $MAPAS.lgpdRedirectReferer;
        
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
