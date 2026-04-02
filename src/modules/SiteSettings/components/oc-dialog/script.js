app.component('oc-dialog', {
    template: $TEMPLATES['oc-dialog'],

    props: {},
    data() {
        return {
            toggle: false
        }
    },
    methods: {
        toggleDialog() {
            this.toggle = !this.toggle
        }
    }
});
