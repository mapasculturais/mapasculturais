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
            name: '',
          },
          steps: [
            { id: 1, name: 'Cadastro', slug: 'step-1' },
          ],
      }
    },

    created() {
        this.entity.useAgentRelationColetivo = this.entity.useAgentRelationColetivo ?? 'dontUse';
        this.entity.useAgentRelationInstituicao = this.entity.useAgentRelationInstituicao ?? 'dontUse';
        this.entity.useSpaceRelationIntituicao = this.entity.useSpaceRelationIntituicao ?? 'dontUse';
    },

    methods: {
        addStep (modal) {
            const id = this.steps.length + 1;
            const nextStep = { ...this.newStep, id, step: `step-${id}` };
            this.newStep = { ...this.newStep, name: '' };
            this.steps = [ ...this.steps, nextStep ];
            modal.close();
        },
    },
});