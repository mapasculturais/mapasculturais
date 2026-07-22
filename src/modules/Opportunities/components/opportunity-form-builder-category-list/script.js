app.component('opportunity-category-list' , {
    template: $TEMPLATES['opportunity-category-list'],

    props: {
        entity: {
            type: Entity,
            required: true
        }
    },

    setup() {
        // os textos estão localizados no arquivo texts.php deste componente
        const text = Utils.getTexts('opportunity-category-list');
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
        enabledButton() {
            const value = (this.category ?? '').trim();
            return value !== '';
        },
        addCategory () {
            const value = (this.category ?? '').trim();

            if (!value) {
                return;
            }

            if (this.entity.registrationCategories.includes(value)) {
                this.clear();
                return;
            }
            
            this.entity.registrationCategories.push(value);
            this.clear();
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