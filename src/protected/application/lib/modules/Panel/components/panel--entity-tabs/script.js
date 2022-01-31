app.component('panel--entity-tabs', {
    template: $TEMPLATES['panel--entity-tabs'],
    emits: [],

    setup(props, { slots }) {
        const hasSlot = name => !!slots[name]
        return { hasSlot }
    },

    created() {
    },

    data() {
        let query = {
            '@permissions': 'view'
        };
        if (this.user) {
            query.user = `EQ(${this.user})` 
        }

        return {
            description: $DESCRIPTIONS[this.type],
            queries: {
                publish: {status: 'EQ(1)', ...query},
                draft: {status: 'EQ(0)', ...query},
                trash: {status: 'EQ(-10)', ...query},
                archived: {status: 'EQ(-2)', ...query},
            }
        }
    },

    props: {
        type: String,
        cacheTls: {
            type: Number,
            default: 5000
        },
        user: {
            type: String,
            default: '@me'
        },
        select: {
            type: String,
            default: 'id,status,name,type,createTimestamp,terms'
        }
    },
    
    methods: {
        showTab (status) {
            if (status == 'publish') {
                return true;
            } else if (typeof this.description?.status == 'undefined') {
                return false;
            } else if (typeof this.description?.status?.options[status] != 'undefined') {
                return true;
            } else {
                return false;
            }
        }
    },
});
