app.component('create-agent' , {
    template: $TEMPLATES['create-agent'],
    emits: ['create'],
    

    setup(props, { slots }) {
        const hasSlot = name => !!slots[name]
        return { hasSlot }
    },
    created() {
        this.createEntity()
        this.iterationFields()
    },

    data() {
        return {
            entity: null,
            fields: [],
        }
    },

    props: {
        editable: {
            type: Boolean,
            default:true
        },
    },
    
    methods: {
        iterationFields() {
            let skip = [
                'createTimestamp', 
                'id',
                'location',
                'name', 
                'shortDescription', 
                'status', 
                'type',
                '_type', 
                'userId',
            ];
            Object.keys($DESCRIPTIONS.agent).forEach((item)=>{
                if(!skip.includes(item) && $DESCRIPTIONS.agent[item].required){
                    this.fields.push(item);
                }
            })
        },
        createEntity() {
            this.entity= new Entity('agent');
            this.entity.terms = {area: []}
        },
        createDraft(modal) {
            this.entity.status = 0;
            this.save(modal);
        },
        createPublic(modal) {
            //lançar dois eventos
            this.entity.status = 1;
            this.save(modal);
        },
        save (modal) {
            this.entity.save().then((response) => {
                modal.close();
                this.$emit('create',response)
            })
        },
        cancel(modal) {
            modal.close()
        },
    },
});
