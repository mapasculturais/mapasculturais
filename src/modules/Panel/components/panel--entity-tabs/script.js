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
            '@order': 'updateTimestamp DESC',
            '@permissions': 'view'
        };
        if (this.user) {
            query.user = `EQ(${this.user})`
        }

        return {
            description: $DESCRIPTIONS[this.type],
            queries: {
                publish: { status: 'GTE(1)', ...query },
                draft: { status: 'EQ(0)', ...query },
                granted: { '@Permissions': '@control', ...query, user: '!EQ(@me)' },
                trash: { status: 'EQ(-10)', ...query },
                archived: { status: 'EQ(-2)', ...query },
            },
            showPrivateKey: false,

        }
    },
    computed: {
        
    },

    props: {
        type: String,
        user: {
            type: [String, Number],
            default: '@me'
        },
        select: {
            type: String,
            default: 'id,status,name,type,createTimestamp,terms,files.avatar,currentUserPermissions'
        },
        tabs: {
            type: String,
            default: "publish,draft,granted,trash,archived"
        },

    },

    methods: {
        
        showTab(status) {
            const tabs = this.tabs.split(',');

            if (tabs.indexOf(status) === -1) {
                return false;
            }

            if (status == 'publish') {
                return true;
            } else if (typeof this.description?.status == 'undefined') {
                return false;
            } else if (typeof this.description?.status?.options[status] != 'undefined') {
                return true;
            } else if (status == 'granted' && this.description.__agentRelations) {
                return true;
            } else {
                return false;
            }
        },

        async moveEntity(entity, event) {
            await event.promise;
            const lists = useEntitiesLists();
            const status = `${entity.status}`;

            const listnames = {
                '1': `${this.type}:publish`,
                '-10': `${this.type}:trash`,
                '-2': `${this.type}:archived`,
                '0': `${this.type}:draft`,
            };

            const list = lists.fetch(listnames[status]);

            entity.removeFromLists([list]);
            
            if (list instanceof Array) {
                list.push(entity);
                entity.$LISTS.push(list);
            }
        }
    },
});
