
app.component('mc-accordion', {
    template: $TEMPLATES['mc-accordion'],

    props: {
        withText: {
            type: Boolean,
            default: false,
        },
    },

    data() {
        return {
            active: false,
        }
    },
    methods: {
        toggle() {
          this.active= !this.active;
        },
    },
});
