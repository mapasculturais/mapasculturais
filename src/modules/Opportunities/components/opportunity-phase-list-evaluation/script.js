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
        },

        tab: {
            type: String,
        },

        /**
         * R02/#65 — guard de instância: false quando este componente é
         * instanciado para a fase de recurso (opportunity-appeal-phase-config),
         * repassado ao opportunity-phase-publish-config-registration.
         */
        mainPhaseOnly: {
            type: Boolean,
            default: true
        },
    },

    data() {
        const statusNames = $MAPAS.config?.opportunityPhaseListEvaluation?.statusNames;
        
        return {
            statusNames
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

        statusKey(code) {
            const dict = {
                10: 'Approved',
                8: 'Waitlist',
                3: 'Notapproved',
                2: 'Invalid',
                1: 'Pending'
            }

            const status = dict[code] ?? false;

            if(status && this.entity.opportunity.summary && this.entity.opportunity.summary[status]) {
                return this.entity.opportunity.summary[status];
            }

            return false;
        }
    }
});