app.component('create-agent' , {
    template: $TEMPLATES['create-agent'],
    emits: [],
    components: {
		
	},
    setup(props, { slots }) {
        const hasSlot = name => !!slots[name]
        return { hasSlot }
    },

    created() {
        this.createInstance()
        

    },

    data() {
        return {
            instance: null,
        }
    },

    props: {
        entity: {
            type: Entity,
            required: true
        },
        editable: {
            type: Boolean,
            default:true
        },
    },
    
    methods: {
        doSomething () {

        },
        createInstance() {
            // this.entity = new Entity("agent");
            this.instance= new Entity('agent');
         
        },
        createDraft(modal) {
            modal.open();
            this.instance.status = 0;
        },
        createPublic(modal) {
            //lançar dois eventos
            modal.open();
            this.instance.status = 1;
            console.log(this.instance);
        },
        save () {

        },
        cancel(modal) {
            modal.close()
        }
    },
});
