app.component('create-occurrence' , {
    template: $TEMPLATES['create-occurrence'],
    emits: ['create'],

    setup() { 
      
    },
    
    created() {
        
    },

    data() {
        return {
            step: 0,
        }
    },

    props: {
        entity: {
            type: Entity,
            required: true
        },

        editable: {
            type: Boolean,
            default: false,
        },
    },
    
    methods: {
        
        cancel(modal) {
            modal.close();
        },

        next() {
            if(this.step < 5) {
                ++this.step
            }
        },

        prev() {
            if(this.step > 0) {
                --this.step;
            }
        }
       
    },
});
