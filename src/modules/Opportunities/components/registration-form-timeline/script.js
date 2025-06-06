app.component('registration-form-timeline', {
    template: $TEMPLATES['registration-form-timeline'],

    props: {
        entity: {
            type: Entity,
            required: true
        },
    },

    computed: {
        formatEditableUntil() {
            return `${this.entity.editableUntil.date('numeric year')} às ${this.entity.editableUntil.time('2-digit')}`
        }
    },

    methods: {
        editForm() {
            const url = Utils.createUrl('registration', 'registrationEdit', [this.entity.id]);
            window.location.href = url.href;
        },

        showButton() {
            return this.entity?.currentUserPermissions?.sendEditableFields;
        }
    },
});
