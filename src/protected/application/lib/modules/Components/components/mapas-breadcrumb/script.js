app.component('mapas-breadcrumb', {
    template: $TEMPLATES['mapas-breadcrumb'],
    
    data() {
        return {
            list: $MAPAS.breadcrumb,
            cover: !!$MAPAS.requestedEntity?.files?.header,
        }
    },
});
