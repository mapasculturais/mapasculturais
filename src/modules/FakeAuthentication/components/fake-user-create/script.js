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

    createUser() {
      const api = new API();

      const validateEmail = (email) => {
        const regex =
          /^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|.(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
        return regex.test(String(email));
      };

      const URL = Utils.createUrl("user", "index");

      if (!this.user.name && !this.user.email) {
        this.messages.error(this.text("errorFormData"));
        return;
      }

      if (!this.user.name) {
        this.messages.error(this.text("errorName"));
        return;
      }
      if (!this.user.email) {
        this.messages.error(this.text("errorEmail"));
        return;
      }
      if (!validateEmail(this.user.email)) {
        this.messages.error(this.text("errorInvalidEmail"));
        return;
      }
      try {
        const data = api
          .POST(URL, this.user)
          .then((res) => res.json())
          .then((data) => {
            if (data?.error) {
              this.error = JSON.stringify(data);
            } else { 
              window.location = Utils.createUrl("painel", "index");
            }
          });
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
