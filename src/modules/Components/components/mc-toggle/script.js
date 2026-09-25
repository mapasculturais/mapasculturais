app.component('mc-toggle', {
    template: $TEMPLATES['mc-toggle'],
    emits: ['update:modelValue'],

    props: {
        label: {
            type: String,
            default: '',
        },
        
        modelValue: {
            type: Boolean, 
            default: false,
        },

        disabled: {
            type: Boolean,
            default: false,
        },
    },

    methods: {
        toggleSwitch(event) {
            if (this.disabled) {
                event.target.checked = this.modelValue;
                return;
            }
            this.$emit('update:modelValue', event.target.checked);
        }
    },

});
