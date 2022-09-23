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
        hasStatus(name) {
            return !! $DESCRIPTIONS[this.entity.__objectType].status.options[name];
        },

        archiveEntity(modal) {
            const entity = this.entity;
            entity.loading = true;
            entity.archive().then(() => {
                this.$emit('archived', {entity, modal});
                entity.loading = false;
            });
        },
        
        deleteEntity(modal) {
            const entity = this.entity;
            entity.loading = true;
            entity.delete(true).then(() => {
                this.$emit('deleted', {entity, modal});
                entity.loading = false;
            });
        },
        
        destroyEntity(modal) {
            const entity = this.entity;
            entity.loading = true;
            entity.destroy().then(() => {
                this.$emit('destroyed', {entity, modal});
                entity.loading = false;
            });
        },

        publishEntity(modal) {
            const entity = this.entity;
            entity.loading = true;
            entity.publish().then(() => {
                this.$emit('published', {entity, modal});
                entity.loading = false;
            });
        },
    },
});
