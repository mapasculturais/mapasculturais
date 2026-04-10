app.component('registration-print-form-sections', {
    template: $TEMPLATES['registration-print-form-sections'],

    props: {
        registration: {
            type: Entity,
            required: true,
        },
    },

    computed: {
        dataCollectionPhases() {
            const phases = [];
            let current = this.registration?.firstPhase ?? this.registration;

            while (current) {
                const opp = current.opportunity;
                if (opp && this._isTruthy(opp.isDataCollection)) {
                    phases.push(current);
                }
                current = current.nextPhase;
            }

            return phases;
        },
    },

    mounted() {
        this.$nextTick(() => {
            requestAnimationFrame(() => {
                window.dispatchEvent(new CustomEvent('registration-print:ready'));
            });
        });
    },

    methods: {
        _isTruthy(value) {
            if (value === true || value === 1 || value === '1') {
                return true;
            }
            if (value === false || value === 0 || value === '0' || value === '' || value == null) {
                return false;
            }
            return !!value;
        },
    },
});
