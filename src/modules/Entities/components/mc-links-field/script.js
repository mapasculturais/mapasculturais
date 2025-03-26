app.component('mc-links-field', {
    template: $TEMPLATES['mc-links-field'],
    emits: ['update:modelValue'],

    props: {
        modelValue: {
            type: Array,
            required: true,
        },

        showTitle: {
            type: Boolean,
            default: false
        },
    },

    methods: {
        addLink() {
            this.$emit('update:modelValue', [ ...this.modelValue, { title: '', value: '' } ]);
        },

        removeLink (index) {
            this.$emit('update:modelValue', this.modelValue.toSpliced(index, 1));
        },
    },
});
