app.component('popover', {
    template: $TEMPLATES['popover'],
    emits: [],

    setup(props, { slots }) {
        const hasSlot = name => !!slots[name]
        return { hasSlot }
    },

    created() {

    },

    data() {
        return {
            active: false
        }
    },

    props: {
        buttonClass: {
            type: String,
            default: 'open popover'
        },
        label: {
            type: String,
            default: 'open popover'
        },
        openside: {
            type: String,
            default: ''
        }
    },
    
    methods: {
        open() {
            this.active = true;
        },
        close() {
            this.active = false;
        },
        toggle() {
            this.active = !this.active;
        }
    },
});
