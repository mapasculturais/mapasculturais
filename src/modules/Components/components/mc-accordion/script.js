
app.component('mc-accordion', {
    template: $TEMPLATES['mc-accordion'],
    emits: ['toggle', 'open', 'close'],

    props: {
        withText: {
            type: Boolean,
            default: false,
        },
        openOnArrow: {
            type: Boolean,
            default: false,
        },
    },

    data() {
        return {
            active: false,
        }
    },
    
    methods: {
        toggle(icon) {
            if (this.openOnArrow && !icon) {
                return; 
            }
            
            this.active = !this.active;
            this.$emit('toggle')
            this.$emit(this.active ? 'open' : 'close')
        },
    },
});
