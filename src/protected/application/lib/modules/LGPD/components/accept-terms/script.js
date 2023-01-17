app.component('accept-terms', {
    template: $TEMPLATES['accept-terms'],
    emits: [],
   
    data() {
        const steps = [];
        const terms = $MAPAS.config.LGPD;
        let accepteds = $MAPAS.hashAccepteds;
        return {
            terms, accepteds,

        };
    },

    props: {

// inicia o componente <componente :entity = "entity"></componente>

    },
    methods: {

        formatDate(timestamp) {
            let date = new McDate(new Date(timestamp * 1000));
            return date.date('numeric year') + ' - ' + date.time('numeric');
        },
        acceptTerm(slug, hash) {
            let url = Utils.createUrl('lgpd', 'accept');
            let api = new API();
            let teste = {};

            // api.GET($MAPAS.baseURL + 'api/user/findOne?@select=*&id=EQ(' + $MAPAS.userId + ')').then(response => response.json().then(validations => {
            //     teste = validations[0];
            //     console.log(validations[0]);

            // }));
            
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
