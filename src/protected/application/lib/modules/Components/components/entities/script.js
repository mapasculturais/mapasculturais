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
app.component('entities', {
    data() {
        return {
            entities: [],
            loading: true
        }
    },

    props: {
        ids: Array,
        type: String,
        select: String
    },
    
    methods: {
        getDataFromApi() {
            const api = new API(this.type);
            
            let query = {};

            if (this.select) {
                query['@select'] = this.select;
            } 

            if (this.ids) {
                query.id = 'IN(' + this.ids.join(',') + ')'
            }

            api.find(query).then(entities => { 
                this.entities = entities;
                this.loading = false;
            });
        }
    },

    created() {
        this.getDataFromApi();
    },

    template: $TEMPLATES['entities']
});
