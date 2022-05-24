app.component('gallery', {
    template: $TEMPLATES['gallery'],
    emits: [],

    setup(props, { slots }) {
        const hasSlot = name => !!slots[name]
        return { hasSlot }
    },

    created() {

    },

    data() {
        return {
            Images: this.entity.files.gallery,
            open: false,
            actualImg: null
        }
    },

    props: {
        entity: {
            type: Entity,
            required: true
        },
        title: {
            type: String,
            default: 'Galeria'
        }
    },
    
    methods: {
        /* click(event) {
            
            document.querySelector('body').classList.toggle("gallery"); 
            document.querySelector('.gallery-full').classList.toggle("active");

            console.log( event.target );
        } */
    },
});
