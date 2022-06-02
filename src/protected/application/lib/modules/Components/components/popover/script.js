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
            message: 'test'
        }
    },

    props: {
        classbnt: {
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
        popover(event) {
            var element = event.target.parentElement.querySelectorAll("#popover")[0];
            element.classList.toggle("active");
        }
    },
});
