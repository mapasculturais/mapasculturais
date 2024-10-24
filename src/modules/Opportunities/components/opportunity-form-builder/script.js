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
          newStep: { id: 'new', name: '' },
          steps: this.entity.registrationSteps,
      }
    },

    created() {
        this.entity.useAgentRelationColetivo = this.entity.useAgentRelationColetivo ?? 'dontUse';
        this.entity.useAgentRelationInstituicao = this.entity.useAgentRelationInstituicao ?? 'dontUse';
        this.entity.useSpaceRelationIntituicao = this.entity.useSpaceRelationIntituicao ?? 'dontUse';
    },

    mounted () {
        window.addEventListener('message', this.onMessage);
    },

    beforeUnmount () {
        window.removeEventListener('message', this.onMessage);
    },

    computed: {
        stepsWithSlugs: {
            get () {
                return this.steps
                    .map((step) => ({ slug: `section-${step.id}`, step }))
                    .sort((a, b) => a.step.displayOrder - b.step.displayOrder)
            },
            set (value) {
                this.steps = value.map((step) => step.step)
            },
        }
    },

    watch: {
        steps () {
            this.syncStepsCount();
        },
    },

    methods: {
        async addStep (modal) {
            const step = new Entity('registrationstep');
            step.displayOrder = this.steps.length;
            step.name = this.newStep.name;
            step.opportunity = this.entity;
            await step.save();

            this.steps.push(step);
            this.newStep.name = '';
            modal.close();
        },
        onMessage ({ data }) {
            if (data.type === 'formbuilder:started') {
                this.syncStepsCount();
            } else if (data.type === 'formbuilder:removeStep') {
                const stepId = data.payload.step_id;
                this.steps = this.steps.filter((step) => step.id !== stepId);
            }
        },
        syncStepsCount () {
            const message = { type: 'formbuilder:countSteps', payload: { count: this.steps.length } };

            this.$el.querySelectorAll('iframe').forEach((iframe) => {
                iframe.contentWindow.postMessage(message);
            });
        },
    },
});