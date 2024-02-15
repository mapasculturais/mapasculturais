app.component('mc-currency-input', {
    template: $TEMPLATES['mc-currency-input'],
    emits: ['change', 'input', 'keydown', 'keyup', 'focus', 'blur', 'update:modelValue'],
    props: {
        modelValue: [String, Number],
        options: Object
    },
    setup(props) {
        const config = {
            currency: $MAPAS.config.currency,
            locale: $MAPAS.config.locale,
            autoDecimalDigits: true,
            ...props.options
        };
        const { inputRef } = CurrencyInput.useCurrencyInput(config)

        return { inputRef }
    },
    methods: {
        dispatchEvent(m, e) {
            this.$emit(m, e);
        }
    }
});
