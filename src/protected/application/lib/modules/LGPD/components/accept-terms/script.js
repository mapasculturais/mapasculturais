app.component('accept-terms', {
    template: $TEMPLATES['accept-terms'],
    emits: [],

    data() {
   
        const terms = $MAPAS.config.LGPD;

        return {
            terms, 
            
        };
    },

    props: {
 

    },
    methods: {

        formatDate(timestamp) {
            let date = new McDate(new Date(timestamp * 1000));
            return date.date('numeric year') + ' - ' + date.time('numeric');
        },
        acceptTerm(slug) {
            let url = Utils.createUrl('lgpd', 'accept');
            let api = new API();

            api.GET($MAPAS.baseURL+'api/user/find?@select=*&id=EQ('+$MAPAS.userId+')').then(async response => response.json().then(validations => { 
                console.log(validations);

            }));



            // let user = new API('user');
            // api.findOne($MAPAS.userId).then( function(response){
            //     console.log(user.id);
            // });
            // api.POST(url,[slug]).then(async function (response) {
            //     console.log(response);
            // });
            // console.log(url);
            // console.log(this.user);
        }


    },
});
