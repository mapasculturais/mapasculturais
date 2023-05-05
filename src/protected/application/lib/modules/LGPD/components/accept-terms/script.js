app.component('accept-terms', {
    template: $TEMPLATES['accept-terms'],
    emits: [],
   
    data() {
        const global = useGlobalState();
        const terms = $MAPAS.config.LGPD;
        const accepteds = $MAPAS.hashAccepteds;
        const step = this.getStep();
        const user = global.auth.user;
        return {
            loading: false,
            user, terms, accepteds, step
        };
    },

    methods: {
        formatDate(timestamp) {
            let date = new McDate(new Date(timestamp * 1000));
            return date.date('numeric year') + ' - ' + date.time('numeric');
        },
        acceptTerm(slug, hash) {
            let url = Utils.createUrl('lgpd', 'accept');
            let api = new API();
            this.loading = true;
            api.POST(url, [slug]).then(res => res.json()).then(data => {
                this.loading = false;
                this.accepteds.push(hash);

                let finish = true;
                for (let termSlug in this.terms) {
                    const term = this.terms[termSlug];
                    if (!this.accepteds.includes(term.md5)) {
                        finish = false;
                        window.location.hash = termSlug;
                        break;
                    }
                }
                if(finish) {
                    window.location.href = data.redirect;
                }
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
        },
    },
});
