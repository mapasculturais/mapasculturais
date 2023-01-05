app.component('form-valid-period', {
    template: $TEMPLATES['form-valid-period'],
    props: {
        entity: {
            type: Entity,
            required: true
        },
        classes: {
            type: [String, Array, Object],
            required: false
        },
    },
    watch: {
        entity: {
            handler(entity) {
                if(entity && entity.validPeriod > 0) {
                    this.requiredPeriod = true
                }
            }
        }
    },
    data () {
        return {
            requiredPeriod: false
        }
    }
});