app.component('panel--entity-actions', {
    template: $TEMPLATES['panel--entity-actions'],
    emits: ['deleted', 'destroyed', 'undeleted', 'published', 'archived', 'unpublished'],

    data(){
        return {
            archiveButton: this.buttons.indexOf('archive') >= 0,
            undeleteButton: this.buttons.indexOf('undelete') >= 0,
            deleteButton: this.buttons.indexOf('delete') >= 0,
            destroyButton: this.buttons.indexOf('destroy') >= 0,
            publishButton: this.buttons.indexOf('publish') >= 0,
            draftButton: this.buttons.indexOf('unpublish') >= 0,
        }
    },

    props: {
        buttons: {
            type: String,
            default: "archive,undelete,delete,destroy,publish"
        },
        entity: {
            type: Entity,
            default: null
        },
        archive: String,
        undelete: String,
        delete: String,
        destroy: String,
        publish: String,
        unpublish: String,

        onDeleteRemoveFromLists: {
            type: Boolean,
            default: true
        }
    },
    
    methods: {
        hasStatus(name) {
            return $DESCRIPTIONS[this.entity.__objectType].status.options[name] !== undefined;
        },

        archiveEntity(modal) {
            const entity = this.entity;
            const promise = entity.archive();
            this.$emit('archived', {entity, modal, promise});
        },
        
        deleteEntity(modal) {
            const entity = this.entity;
            const promise = entity.delete(this.onDeleteRemoveFromLists)
            this.$emit('deleted', {entity, modal, promise});
        },
        
        undeleteEntity(modal) {
            const entity = this.entity;
            const promise = entity.undelete();
            this.$emit('undeleted', {entity, modal, promise});
        },
        
        destroyEntity(modal) {
            const entity = this.entity;
            const promise = entity.destroy()
            this.$emit('destroyed', {entity, modal, promise});
        },

        publishEntity(modal) {
            const entity = this.entity;
            const promise = entity.publish();
            this.$emit('published', {entity, modal, promise});
        },

        unpublishEntity(modal) {
            const entity = this.entity;
            const promise = entity.unpublish()
            this.$emit('unpublished', {entity, modal, promise});
        },
    },
});
