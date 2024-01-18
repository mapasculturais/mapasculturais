app.component("fake-user-create", {
  template: $TEMPLATES["fake-user-create"],

  // define os eventos que este componente emite
  emits: ["create"],

  props: { },

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

  computed: { },

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
              this.error = JSON.stringify(data);
              this.messages.error(this.text("error"));
            } else {
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
      setTimeout(() => (this.entity = null), 200);
    },
    iterationFields() {
      let skip = ["name", "email"];
    },
  },
});
