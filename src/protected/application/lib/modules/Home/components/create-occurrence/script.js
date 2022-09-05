app.component('create-occurrence', {
    template: $TEMPLATES['create-occurrence'],
    
    async created(){
        // const spaceAPI = new API('event');
        // const query = this.query;
        // query['@select'] = 'description';
        // console.log(await this.spaceAPI.find(query));

    }, 
 
    
    setup() { 
      
    },
    data() {
        return;
    },
    props: {
        entity: {
            type: Entity,
            required: true 
        },
        editable: {
            type: Boolean,
            default:true,
        },
    },

    methods: {
         findOccurence() {
             
         },
    },
});
