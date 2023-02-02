app.component('seal-form-valid-period', {
    template: $TEMPLATES['seal-form-valid-period'],
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
      'entity': {
          handler(newValue) {
              if(newValue.validPeriod > 0) {
                  this.requiredPeriod = true
              }
          },
          immediate: true
      }
    },
    data () {
        return {
            requiredPeriod: false
        }
    }
});