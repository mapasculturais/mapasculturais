app.component('registration-quotas-card', {
    template: $TEMPLATES['registration-quotas-card'],
    props: {
        entity: {
            type: Entity,
            required: true,
        },
    },
    watch: {
        'entity.appliedForQuota' (value) {
            const appliedForQuota = typeof value === 'string' ? JSON.parse(value) : value;
            const formIframe = document.querySelector('iframe#registration-form');
            formIframe?.contentWindow.postMessage({ type: 'registration.appliedForQuota', appliedForQuota });
        },
    },
});
