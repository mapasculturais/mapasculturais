app.component('registration-header', {
    template: $TEMPLATES['registration-header'],

    props: {
        registration: {
            type: Entity,
            required: true
        },
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
