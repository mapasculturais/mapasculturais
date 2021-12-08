/**
 * uso:
 * 
 * // omitindo o id, pega a entity do Mapas.requestedEntity
 * <entity v-slot='{entity}'>{{entity.id}} - {{entity.name}}</entity>
 * 
 * // passango o id sem select, não faz consulta na api
 * <entity v-slot='{entity}' :type='space' :id='33'>{{entity.id}}</entity>
 * 
 * // passando o id e passando um select, faz a consulta na api
 * <entity v-slot='{entity}' :type='space' :id='33' :select="id,name">{{entity.id}} - {{entity.name}}</entity>
 */
app.component('entity', {
    data() {
        return {
            entity: null,
            loading: true
        }
    },

    props: {
        id: Number,
        type: String,
        select: String
    },

    methods: {
        getDataFromApi() {
            var api = new API(this.type);
            api.findOne(this.id).then(entity => { 
                this.entity = entity;
                this.loading = false;
            }).catch((error) => {
                console.log(error);
            });
        }
    },

    created() {
        this.getDataFromApi();
    },

    template: $TEMPLATES['entity']
});
