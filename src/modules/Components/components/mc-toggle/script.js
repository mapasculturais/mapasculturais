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
    },

    data() {
        return {
        }
    },

    methods: {
        toggleSwitch(event) {
            this.$emit('update:modelValue', event.target.checked);
        }
    },

});
