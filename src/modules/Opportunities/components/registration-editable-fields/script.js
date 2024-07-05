app.component('registration-editable-fields', {
    template: $TEMPLATES['registration-editable-fields'],

    props: {
        registration: {
            type: Entity,
            required: true,
        },
    },
    
    setup() {
        const messages = useMessages();
        return { messages }
    },

    data() {
        const fields = $MAPAS.config.registrationEditableFields;

        return {
            fields,
            selectedFields: this.registration.editableFields ?? [],
            editableUntil: this.registration.editableUntil ? this.registration.editableUntil._date : null,
        }
    },

    computed: {
        openToEdit() {
            return !this.registration.editableUntil ? false : true;
        },
        sent() {
            return !this.registration.editSentTimestamp ? false : true;
        },
        afterDeadline() {
            return this.registration.editableUntil && new McDate(this.registration.editableUntil).isPast() ? true : false;
        },
        canReopen() {
            return this.registration.editSentTimestamp && new McDate(this.registration.editableUntil).isFuture() ? true : false;
        }
    },
    
    methods: {
        save(modal) {
            if (this.selectedFields.length == 0) {
                this.messages.error(__('campos para edição','registration-editable-fields'));
                return false;
            }

            if (!this.editableUntil) {
                this.messages.error(__('data limite','registration-editable-fields'));
                return false;
            }

            this.registration.editableFields = this.selectedFields;
            this.registration.editableUntil = this.editableUntil;

            this.registration.save();
            modal.close();
        },

        reopen(modal) {
            this.registration.editSentTimestamp = null;
            this.registration.save();
            modal.close();
        },
    },
});
