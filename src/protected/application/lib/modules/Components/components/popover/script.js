app.component('popover', {
    template: $TEMPLATES['popover'],
    emits: ['open', 'close'],

    setup(props, { slots }) {
        const hasSlot = name => !!slots[name]
        return { hasSlot }
    },

    data() {
        return {
            active: false,
            click: -1,
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
            let contained = false;
            const slotPopover = document.getElementsByClassName('v-popper__popper');
            
            for (let popover of slotPopover) {
                if (popover.contains(event.target)) { 
                    contained = true;
                }
            };
            
            if (!contained) { 
                
                this.close();
            };
        }),
        document.addEventListener('keydown', (e) => {
            if((e.key=="27") || (e.key =="Escape")) {
                this.close();
            }            
        });
    },

    methods: {
        focus() {
            const inputs = this.$refs.content.getElementsByTagName('input');
            if (inputs.length) {
                setTimeout(() => {
                    inputs[0].focus();
                }, 100);
            }
        },
        open() {
            if((this.click)%2==0){
                this.active = true;
                this.$emit('open', this);
            }

        },
        close() {
            this.click++;
            this.active = false;
            this.$emit('close', this);
        },
        toggle() {
            this.active ? this.close() : this.open();
        }
    },
});
