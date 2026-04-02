app.component('oc-popover', {
    template: $TEMPLATES['oc-popover'], // Certifique-se de que o HTML correto está vinculado

    props: {
        position: {
            type: String,
            default: 'bottom', // Posição padrão (abaixo)
            validator(value) {
                return ['top', 'right', 'bottom', 'left'].includes(value); // Validações
            }
        }
    },

    data() {
        return {
            toggle: false // Inicialmente fechado
        };
    },

    methods: {
        togglePopover() {
            this.toggle = !this.toggle;
        }
    }
});
