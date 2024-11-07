app.component('opportunity-phase-list-evaluation' , {
    template: $TEMPLATES['opportunity-phase-list-evaluation'],

    setup() {
        const text = Utils.getTexts('opportunity-phase-list-evaluation');
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
        }
    },

    methods: {
        sync(opportunity) {
            api = new API('opportunity');
            let url = api.createUrl('syncRegistrations', {id: opportunity._id});

            var args = {};
            api.POST(url, args).then(res => res.json()).then(data => {
                const messages = useMessages();
                messages.success(this.text('success'));
                window.location.reload();
            });
        },
    }
});