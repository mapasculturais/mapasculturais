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
            '@order': 'name ASC',
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
        user: {
            type: [String, Number],
            default: '@me'
        },
        select: {
            type: String,
            default: 'id,status,name,type,createTimestamp,terms,files.avatar,currentUserPermissions'
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
        },

        moveEntity (entity) {
            const lists = useEntitiesLists();
            const status = `${entity.status}`;

            const listnames = {
                '1': `${this.type}:publish`,
                '-10':  `${this.type}:trash`,
                '-2':  `${this.type}:archived`,
            };
            
            const list = lists.fetch(listnames[status]);
            
            entity.removeFromLists();

            if(list instanceof Array) {
                list.push(entity);
                entity.$LISTS.push(list);
            }
        }
    },
});
