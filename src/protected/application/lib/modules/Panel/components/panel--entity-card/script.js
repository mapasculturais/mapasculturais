app.component('panel--entity-card', {
    template: $TEMPLATES['panel--entity-card'],
    emits: ['deleted', 'destroyed', 'published', 'unpublished', 'archived' ],

    props: {
        entity: {
            type: Entity,
            required: true
        },

        onDeleteRemoveFromLists: {
            type: Boolean,
            default: true
        }
    },

    computed: {
        leftButtons() {
            let buttons = 'archive,delete,destroy';

            if(this.entity.status === 0) {
                buttons += ',publish';
            }

            return buttons;
        },

        rightButtons() {
            const status = this.entity.status;

            if (status == -10) {
                return 'undelete';

            } else if (status == -2) {
                return 'publish';
            }
        }
    }
})
