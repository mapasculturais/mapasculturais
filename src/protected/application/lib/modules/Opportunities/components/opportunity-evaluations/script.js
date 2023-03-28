app.component('opportunity-evaluations' , {
    template: $TEMPLATES['opportunity-evaluations'],
    props: {
        entity: {
            type: Entity,
            required: true
        }
    },
    setup() {
        // os textos estão localizados no arquivo texts.php deste componente
        const text = Utils.getTexts('opportunity-evaluations');
        return { text }
    },
    data () {
      return {
      }
    },
    mounted () {

    },
    computed: {

    }
});