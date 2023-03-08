app.component('opportunity-header', {
    template: $TEMPLATES['opportunity-header'],

    props: {
        opportunity: {
            type: Entity,
            required: true
        },
    },
    data(){
    },
    mounted(){

    },

    methods:{
        historyBack(){
            if (window.history.length > 2) {
                window.history.back();
            } else {
                window.location.href = $MAPAS.baseURL+'panel';
            }
        }
    }

});
