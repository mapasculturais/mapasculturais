app.component('opportunity-form-builder' , {
    template: $TEMPLATES['opportunity-form-builder'],
    props: {
        entity: {
            type: Entity,
            required: true
        }
    },
    setup() {
        // os textos estão localizados no arquivo texts.php deste componente
        const text = Utils.getTexts('opportunity-form-builder');
        return { text }
    },
    data () {
      return {
          newStep: {
            id: 2,
            name: '',
          },
          steps: [
            { id: 1, name: 'Cadastro' },
          ],
      }
    },

    created() {
        this.entity.useAgentRelationColetivo = this.entity.useAgentRelationColetivo ?? 'dontUse';
        this.entity.useAgentRelationInstituicao = this.entity.useAgentRelationInstituicao ?? 'dontUse';
        this.entity.useSpaceRelationIntituicao = this.entity.useSpaceRelationIntituicao ?? 'dontUse';
    },

    computed: {
        stepsWithSlugs: {
            get () {
                return this.steps.map((step) => ({ slug: `section-${step.id}`, step }))
            },
            set (value) {
                this.steps = value.map((step) => step.step)
            },
        }
    },

    methods: {
        addStep (modal) {
            this.steps = [ ...this.steps, { ...this.newStep } ];
            this.newStep = { id: this.newStep.id + 1, name: '' };
            modal.close();
        },
    },
});