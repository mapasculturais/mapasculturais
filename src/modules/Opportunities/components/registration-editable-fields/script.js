app.component('registration-editable-fields', {
    template: $TEMPLATES['registration-editable-fields'],

    props: {
        registration: {
            type: Entity,
            required: true,
        },
    },
    
    setup() {
        const text = Utils.getTexts('registration-editable-fields')
        const messages = useMessages();
        return { messages, text }
    },

    data() {
        const fields = $MAPAS.config.registrationEditableFields;

        return {
            fields,
            selectedFields: this.registration.editableFields ?? [],
            processing: false,
            selectAll: false
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
            return this.registration.editableUntil && this.registration.editableUntil.isPast() ? true : false;
        },
        canReopen() {
            return this.registration.editSentTimestamp && this.registration.editableUntil.isFuture() ? true : false;
        }
    },
    
    methods: {
        async save(modal) {

            if (this.selectedFields.length == 0) {
                this.messages.error(__('campos para edição','registration-editable-fields'));
                return false;
            }

            if (!this.registration.editableUntil) {
                this.messages.error(__('data limite','registration-editable-fields'));
                return false;
            }

            this.registration.editableFields = this.selectedFields;

            this.processing = 'saving';
            await this.registration.save();
            this.processing = false;
            modal.close();
        },

        reopen() {
            this.processing = 'reopening';
            this.registration.POST('reopenEditableFields', {callback: (response) => {
                this.registration.editSentTimestamp = null;
                this.processing = false;
                this.messages.success(this.text('campos reabertos para edição'));
            }})
        },

        updateAllSelection() {
            if (this.selectAll) {
                this.selectedFields = this.fields.map(field => field.ref);
            } else {
                this.selectedFields = this.registration.editableFields ?? [];
            }
        }
    },
});
