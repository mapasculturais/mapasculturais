app.component('opportunity-subscription-list' , {
    template: $TEMPLATES['opportunity-subscription-list'],

    data () {
        return {}
    },

    props: {
    },

    computed: {
        isLogged() {
            return $MAPAS.userId != null
        }
    }
});