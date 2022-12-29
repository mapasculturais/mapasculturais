app.component('form-valid-period', {
    template: $TEMPLATES['form-valid-period'],
    props: {
        entity: {
            type: Entity,
            required: true
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
    // mounted() {
    //     if(this.props.entity && this.props.entity.validPeriod > 0) {
    //         this.requiredPeriod = true
    //     }
    //
    // },
    // computed: {
    //     requiredPeriod: {
    //         // getter
    //         get() {
    //             return this.props.entity && this.props.entity.validPeriod >= 0
    //         },
    //         // setter
    //         set(newValue) {
    //             [this.firstName, this.lastName] = newValue.split(' ')
    //
    //         }
    //     }
    // },
    data () {
        return {
            requiredPeriod: false
        }
    }
});