app.component('mc-breadcrumb', {
    template: $TEMPLATES['mc-breadcrumb'],
    
    data() {
        return {
            list: $MAPAS.breadcrumb,
            cover: !!$MAPAS.requestedEntity?.files?.header,
        }
    },
});
