app.component('opportunity-category' , {
    template: $TEMPLATES['opportunity-category'],
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
          phase: null,
          timeout: null
      }
    },

    methods: {
        autoSave(){
            clearTimeout(this.timeout);
                this.timeout = setTimeout(()=>{
                    this.entity.save();
            },1500);
        }
    }
});