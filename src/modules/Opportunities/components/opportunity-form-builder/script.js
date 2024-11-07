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
        const steps = this.entity.registrationSteps?.sort((a, b) => a.displayOrder - b.displayOrder) || [];

        return {
            newStep: { id: 'new', name: '' },
            steps,
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
                return this.steps.map((step) => ({ slug: `section-${step.id}`, step }));
            },
            set (value) {
                this.steps = value.map((step) => step.step);
            },
        }
    },

    watch: {
        steps () {
            this.steps.forEach(async (step, index) => {
                if (index !== step.displayOrder) {
                    const entity = new Entity('registrationstep');
                    entity.populate(step);
                    entity.displayOrder = index;
                    await entity.save();
                }
            });
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

            this.$nextTick(() => {
                const tabsComp = this.$refs.tabs;
                tabsComp.activeTab = tabsComp.tabs[this.steps.length - 1];
            });
        },
        async deleteStep (step) {
            const stepId = step.id;
            await step.delete(true);

            this.steps = this.steps.filter((step) => step.id && step.id !== stepId);

            this.$nextTick(() => {
                const tabsComp = this.$refs.tabs;
                tabsComp.activeTab = tabsComp.tabs[0];
            });
        },
    },
});