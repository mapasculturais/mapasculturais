app.component('registration-autosave-notification', {
    template: $TEMPLATES['registration-autosave-notification'],
    
    props: {
        registration: {
            type: Entity,
            required: true
        },
    },

    data() {
        return {
            resultTime: $MAPAS.config.registrationAutosaveNotification.autosaveDebounce,
        }
    },
    
});
