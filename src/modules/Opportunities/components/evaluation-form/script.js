app.component('evaluation-form', {
    template: $TEMPLATES['evaluation-form'],

    props: {
        entity: {
            type: [Entity, Object],
            required: true,
        },
    },

    mounted() {
        window.addEventListener('resize', () => this.resizeForm());
        this.resizeForm();
    },

    updated() {
        this.resizeForm();
    },
    
    setup(props, { slots }) {
        const hasSlot = name => !!slots[name];
        const text = Utils.getTexts('evaluation-form');
        return { text, hasSlot }
    },

    data() {
        const global = useGlobalState();
        return {
            formData: {},
            validateErrors: global.validateEvaluationErrors,
        }
    },

    methods: {
        resizeForm() {
            const buttonsHeight = this.$refs.buttons.offsetHeight;
            const headerHeight = this.$refs.header.offsetHeight;
            const height = Math.max(window.innerHeight - buttonsHeight - headerHeight - 200, 500); 
            this.$refs.form.style.height = height + 'px';
        }
    }
});
