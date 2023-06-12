app.component('opportunity-category' , {
    template: $TEMPLATES['opportunity-form-builder-category'],
    props: {
        entity: {
            type: Entity,
            required: true
        }
    },
    setup() {
        const text = Utils.getTexts('opportunity-category');
        return { text }
    },

    created() {
      this.phase = $DESCRIPTIONS.opportunity;
    },

    data () {
      return {
          phase: null
      }
    }
});