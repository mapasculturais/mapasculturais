app.component('opportunity-phase-list-registrations' , {
    template: $TEMPLATES['opportunity-phase-list-registrations'],

    setup() {
        const text = Utils.getTexts('opportunity-phase-list-registrations');
        return { text };
    },

    props: {
        entity: {
            type: Entity,
            required: true
        },
        phases: {
            type: Array,
            required: true
        },
        tab: {
            type: String,
        }
    },

    computed: {
        index() {
            return this.phases.indexOf(this.entity);
        },

        previousPhase() {
            return this.phases[this.index - 1];
        },

        nextPhase() {
            return this.phases[this.index + 1];
        },
    },
    methods: {
        sync() {
            console.log("AAA")
            // return Utils.createUrl('opportunity', 'syncRegistrations', [this.entity.id]);
            api = new API('opportunity');
            let url = api.createUrl('syncRegistrations', {id: this.entity.id});

            var args = {};
            api.POST(url, args).then(res => res.json()).then(data => {
                const messages = useMessages();
                messages.success(this.text('success'));
                window.location.reload();
            });
        },
    },
});