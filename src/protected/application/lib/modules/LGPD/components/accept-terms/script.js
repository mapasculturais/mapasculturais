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
            
            api.POST(url, [slug]).then(async function (response) {
                console.log(response);
                
            });
            this.accepteds.push(hash);
                console.log(hash);
        
        },
        showButton(hash) {
            if (this.accepteds.includes(hash)) {
                return false;
            }
            return true;

        },

    },
});
