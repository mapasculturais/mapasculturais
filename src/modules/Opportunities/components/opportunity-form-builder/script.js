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
            newStep: { id: 'new', name: '', metadata: {} },
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

            this.sendStepsMessage();
        },
    },

    beforeMount () {
        window.addEventListener('message', this.receiveMessage);
        this.sendStepsMessage();
    },

    beforeUnmount () {
        window.removeEventListener('message', this.receiveMessage);
    },

    methods: {
        async addStep (modal) {
            const step = new Entity('registrationstep');
            step.displayOrder = this.steps.length;
            step.name = this.newStep.name;
            step.metadata = this.newStep.metadata;
            step.opportunity = this.entity;
            await step.save();

            this.steps.push(step);

            this.newStep.metadata = {};
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
        receiveMessage ({ data: event }) {
            if (event.type === 'opportunity-form:iframeLoaded') {
                this.sendStepsMessage();
            }
        },
        saveMetadata (step) {
            step.metadata = { ...step.metadata }; // Sometimes PHP serializes metadata as empty JSON array
            step.save();
        },
        sendMessage (type, data) {
            document.querySelectorAll('iframe').forEach((iframe) => {
                iframe.contentWindow.postMessage({ type, data });
            });
        },
        sendStepsMessage () {
            const simpleSteps = this.steps.map(({ __objectType, id, name }) => ({ __objectType, id, name }));
            this.sendMessage('opportunity-form:steps', simpleSteps);
        },
    },
});