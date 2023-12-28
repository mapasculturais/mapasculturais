/**
 * Vue Lifecycle
 * 1. setup
 * 2. beforeCreate
 * 3. created
 * 4. beforeMount
 * 5. mounted
 *
 * // sempre que há modificação nos dados
 *  - beforeUpdate
 *  - updated
 *
 * 6. beforeUnmount
 * 7. unmounted
 */

app.component("fake-user-create", {
  template: $TEMPLATES["fake-user-create"],

  // define os eventos que este componente emite
  emits: ["create"],

  props: {
    /* 
    textoLocalizado: {
      type: String,

      // nas propriedades não é possível utilizar o this.text
      default: __("texto localizado", "fake-user-create"),
    },

  

    lastname: {
      type: String,
    },

    nickname: {
      type: String,
    }, */
  },

  setup(props, { slots }) {},

  beforeCreate() {},
  created() {
    this.iterationFields();
  },

  beforeMount() {},
  mounted() {},

  beforeUpdate() {},
  updated() {},

  beforeUnmount() {},
  unmounted() {},
  setup() {
    // os textos estão localizados no arquivo texts.php deste componente
    const messages = useMessages();
    const text = Utils.getTexts("fake-user-create");
    return { text, messages };
  },
  data() {
    return {
      user: {
        name: "",
        email: "",
      },
      error: "",
    };
  },

  computed: {
    /*    compareDisplayName() {
      return this.entity.name == this.displayName;
    },

    compareFullname() {
      return this.entity.nomeCompleto == this.fullname;
    }, */
  },

  methods: {
    createEntity() {
      this.entity = new Entity("app");
      this.entity.type = 1;
      this.entity.terms = { area: [] };
    },
    createUser(e) {
      e.preventDefault();
      const api = new API();
      const URL = Utils.createUrl("user", "index");
      try {
        const data = api
          .POST(URL, this.user)
          .then((res) => res.json())
          .then((data) => {
            if (data?.error) {
              console.log("setStatusError");
              this.error = JSON.stringify(data);
              this.messages.error(this.text("error"));
            } else {
              console.log(data);
              console.log("setStatusSuccess");
              console.log(window);
              window.location = Utils.createUrl("painel", "index");
            }
          });
        this.user = {
          name: "",
          email: "",
        };
      } catch (e) {
        console.log(e);
      }
    },
    destroyEntity() {
      // para o conteúdo da modal não sumir antes dela fechar
      setTimeout(() => (this.entity = null), 200);
    },
    iterationFields() {
      let skip = ["name", "email"];
      /* Object.keys($DESCRIPTIONS.project).forEach((item) => {
        if (!skip.includes(item) && $DESCRIPTIONS.project[item].required) {
          this.fields.push(item);
        }
      }); */
    },
  },
});
