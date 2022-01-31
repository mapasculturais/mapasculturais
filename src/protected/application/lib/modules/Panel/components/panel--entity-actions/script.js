app.component('panel--entity-actions', {
    template: $TEMPLATES['panel--entity-actions'],
    emits: ['deleted', 'destroyed', 'published', 'archived'],

    data(){
        return {
            archiveButton: this.buttons.indexOf('archive') >= 0,
            deleteButton: this.buttons.indexOf('delete') >= 0,
            destroyButton: this.buttons.indexOf('destroy') >= 0,
            publishButton: this.buttons.indexOf('publish') >= 0,
        }
    },

    props: {
        buttons: {
            type: String,
            default: "archive,delete,destroy,publish"
        },
        entity: {
            type: Entity,
            default: null
        },
        archive: String,
        delete: String,
        destroy: String,
        publish: String,
    },
    
    methods: {
        archiveEntity(modal) {
            const entity = this.entity;
            entity.loading = true;
            entity.archive().then(() => {
                entity.loading = false;
                this.$emit('archived', {entity, modal});
            });
        },
        
        deleteEntity(modal) {
            const entity = this.entity;
            entity.loading = true;
            entity.delete().then(() => {
                entity.loading = false;
                this.$emit('deleted', {entity, modal});
            });
        },
        
        destroyEntity(modal) {
            const entity = this.entity;
            entity.loading = true;
            entity.destroy().then(() => {
                entity.loading = false;
                this.$emit('destroyed', {entity, modal});
            });
        },

        publishEntity(modal) {
            const entity = this.entity;
            entity.loading = true;
            entity.publish().then(() => {
                entity.loading = false;
                this.$emit('published', {entity, modal});
            });
        },
    },
});
