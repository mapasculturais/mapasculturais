app.component('opportunity-form-builder-category-list' , {
    template: $TEMPLATES['opportunity-form-builder-category-list'],

    setup() {
        // os textos estão localizados no arquivo texts.php deste componente
        const text = Utils.getTexts('opportunity-form-builder-category-list');
        return { text }
    },
    created () {
      this.categories = $MAPAS.requestedEntity.registrationCategories || {};
    },
    data () {
      return {
          categories: [],
          category: null
      };
    },
    methods: {
      addCategory () {
          this.categories.push(this.category);
      },
      clear () {
          this.category = null;
      },
      deleteItem (item) {
          const index = this.categories.indexOf(item);
          this.categories.splice(index, 1);
      }
    },
    watch: {
        categories: {
            handler(val) {
                $MAPAS.requestedEntity.registrationCategories = val;
            }
        }
    }
});