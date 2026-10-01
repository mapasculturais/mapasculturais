
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
        open: {
            type: Boolean,
            default: false,
        },
    },

    data() {
        return {
            active: this.open,
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
