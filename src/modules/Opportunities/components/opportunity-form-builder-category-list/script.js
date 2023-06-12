app.component('opportunity-form-builder-category-list' , {
    template: $TEMPLATES['opportunity-form-builder-category-list'],

    props: {
        entity: {
            type: Entity,
            required: true
        }
    },

    setup() {
        // os textos estão localizados no arquivo texts.php deste componente
        const text = Utils.getTexts('opportunity-form-builder-category-list');
        return { text }
    },

    created () {
        this.entity.registrationCategories = this.entity.registrationCategories || [];
    },

    data () {
      return {
          category: null
      };
    },
    methods: {
      addCategory () {
          this.entity.registrationCategories.push(this.category);
      },

      clear () {
          this.category = null;
      },

      deleteItem (item) {
          const index = this.entity.registrationCategories.indexOf(item);
          this.entity.registrationCategories.splice(index, 1);
      }
    }
});