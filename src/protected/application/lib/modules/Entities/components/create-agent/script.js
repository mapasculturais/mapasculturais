app.component('create-agent' , {
    template: $TEMPLATES['create-agent'],
    emits: ['create'],
    components: {
		
	},
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
            fields: []
        }
    },

    props: {
        editable: {
            type: Boolean,
            default:true
        },
    },
    
    methods: {
        doSomething () {

        },
        iterationFields() {
            let array = ['type', '_type', 'name'];
            Object.keys($DESCRIPTIONS.agent).forEach((item)=>{
                if(!array.includes(item) && $DESCRIPTIONS.agent[item].required){
                    this.fields.push(item);
                }
            })
        console.log(this.fields);
        },
        createEntity() {
            this.entity= new Entity('agent');
            this.entity.terms = {area: ['Cultura Digital', 'Música']}
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
                console.log(response);
            })
        },
        cancel(modal) {
            modal.close()
        }
    },
});
