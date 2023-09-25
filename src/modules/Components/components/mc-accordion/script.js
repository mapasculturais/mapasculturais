
app.component('mc-accordion', {
    template: $TEMPLATES['mc-accordion'],

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
