app.component('popover', {
    template: $TEMPLATES['popover'],
    emits: ['open', 'close'],

    setup(props, { slots }) {
        const hasSlot = name => !!slots[name]
        return { hasSlot }
    },

    data() {
        return {
            active: false
        }
    },

    props: {
        classes: {
            type: Array,
            default: []
        },
        buttonLabel: {
            type: String,
            default: ''
        },
        buttonClasses: {
            type: Array,
            default: []
        },
        openside: {
            type: String,
            default: '',
            validator: (value) => {
                return [
                    'up-right','up-left',
                    'down-right', 'down-left',
                    'left-up', 'left-down',
                    'right-up', 'right-down',
                ].indexOf(value) >= 0;
            }
        }
    },
    

    mounted() {
        document.addEventListener('mousedown', (event) => {
            const slotPopover = document.getElementsByClassName('v-popper__popper')[0];
            if (!slotPopover) { return }
            if (!slotPopover.contains(event.target)) { this.close(); }
        })
    },

    methods: {
        open() {
            this.active = true;
            this.$emit('open', this);
            this.$nextTick(() => {
                const inputs = this.$el.nextElementSibling.getElementsByTagName('input');
                if (inputs.length) {
                    inputs[0].focus();
                }
            });
        },
        close() {
            this.active = false;
            this.$emit('close', this);
        },
        toggle() {
            this.active ? this.close() : this.open();
        }
    },
});
