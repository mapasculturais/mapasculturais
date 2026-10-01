app.component('seal-locked-field', {
    template: $TEMPLATES['seal-locked-field'],
    props: {
        entity: {
            type: Entity,
            required: true
        },
        classes: {
            type: [String, Array, Object],
            required: false
        },
    },
    setup() {
        const text = Utils.getTexts('seal-locked-field');
        return { text };
    },
    // FIXME: buildLockedFieldsConfig é derivado de agents/spaces/taxonomiesAgents/taxonomiesSpaces.
    // A longo prazo, lockedFieldsConfig deveria ser uma computed property para evitar watchers deep
    // e atribuição manual em this.entity.lockedFieldsConfig. Mantido como débito técnico para
    // preservar o contrato atual e minimizar o refactoring nesta correção.
    watch: {
        agents: {
            handler() {
                this.buildLockedFieldsConfig();
            },
            deep: true
        },
        spaces: {
            handler() {
                this.buildLockedFieldsConfig();
            },
            deep: true
        },
        taxonomiesAgents: {
            handler() {
                this.buildLockedFieldsConfig();
            },
            deep: true
        },
        taxonomiesSpaces: {
            handler() {
                this.buildLockedFieldsConfig();
            },
            deep: true
        }
    },
    data () {
        return {
            spaces: [],
            agents: [],
            taxonomiesAgents: [],
            taxonomiesSpaces: [],
            pendingInvalidatorItem: null,
            pendingInvalidatorValue: false,
            pendingInvalidatorCheckbox: null,
        }
    },
    mounted () {
        // console.log('[seal-locked-field] mounted entity.lockedFieldsConfig:', JSON.parse(JSON.stringify(this.entity.lockedFieldsConfig)));

        this.iterationAgentFields();
        this.iterationSpaceFields();

        const lockedFieldsConfig = this.entity.lockedFieldsConfig || {};
        const lockedFields = [...(this.entity.lockedFields || [])];

        const applyConfig = (items) => {
            items.forEach(item => {
                const config = lockedFieldsConfig[item.fieldKey];
                if (config) {
                    item.value = true;
                    item.hasExpiry = !!config.hasExpiry;
                    item.periodValue = config.hasExpiry ? (parseInt(config.periodValue) || 1) : 1;
                    item.periodUnit = config.hasExpiry ? (config.periodUnit || 'month') : 'month';
                    item.isInvalidator = config.hasExpiry ? !!config.isInvalidator : false;
                } else if (lockedFields.includes(item.fieldKey)) {
                    item.value = true;
                }
            });
        };

        applyConfig(this.agents);
        applyConfig(this.spaces);
        applyConfig(this.taxonomiesAgents);
        applyConfig(this.taxonomiesSpaces);
    },
    beforeUnmount() {
        // Sincroniza o estado mais recente na entidade antes da destruição,
        // evitando perda de alterações quando a aba não utiliza cache.
        this.buildLockedFieldsConfig();
    },
    methods: {
        generateItemUid(item) {
            item.uid = `${Utils.uid()}-${item.fieldKey.replace(/[^a-zA-Z0-9_-]/g, '-')}`;
        },
        iterationAgentFields() {
            const skip = $MAPAS.config.sealLockedSkipedFields.agents;
            Object.keys($DESCRIPTIONS.agent).forEach((item)=>{
                if(!skip.includes(item) && !$DESCRIPTIONS.agent[item].isEntityRelation ){
                    const fieldItem = {
                        fieldKey: `agent.${item}`,
                        value: false,
                        hasExpiry: false,
                        periodValue: 1,
                        periodUnit: 'month',
                        isInvalidator: false,
                        label: $DESCRIPTIONS.agent[item].label,
                        item
                    };
                    this.generateItemUid(fieldItem);
                    this.agents.push(fieldItem);
                }
            })
            Object.keys($TAXONOMIES).forEach((item)=>{
                if(!skip.includes(item)){
                    const fieldItem = {
                        fieldKey: `agent.terms:${item}`,
                        value: false,
                        hasExpiry: false,
                        periodValue: 1,
                        periodUnit: 'month',
                        isInvalidator: false,
                        label: $TAXONOMIES[item].description,
                        item
                    };
                    this.generateItemUid(fieldItem);
                    this.taxonomiesAgents.push(fieldItem);
                }
            })
        },
        iterationSpaceFields() {
            const skip = $MAPAS.config.sealLockedSkipedFields.spaces;

            Object.keys($DESCRIPTIONS.space).forEach((item)=>{
                if(!skip.includes(item) && !$DESCRIPTIONS.space[item].isEntityRelation){
                    const fieldItem = {
                        fieldKey: `space.${item}`,
                        value: false,
                        hasExpiry: false,
                        periodValue: 1,
                        periodUnit: 'month',
                        isInvalidator: false,
                        label: $DESCRIPTIONS.space[item].label,
                        item
                    };
                    this.generateItemUid(fieldItem);
                    this.spaces.push(fieldItem);
                }
            })
            Object.keys($TAXONOMIES).forEach((item)=>{
                if(!skip.includes(item)){
                    const fieldItem = {
                        fieldKey: `space.terms:${item}`,
                        value: false,
                        hasExpiry: false,
                        periodValue: 1,
                        periodUnit: 'month',
                        isInvalidator: false,
                        label: $TAXONOMIES[item].description,
                        item
                    };
                    this.generateItemUid(fieldItem);
                    this.taxonomiesSpaces.push(fieldItem);
                }
            })
        },
        onFieldChange(item) {
            if (!item.value) {
                item.hasExpiry = false;
                item.periodValue = 1;
                item.periodUnit = 'month';
                item.isInvalidator = false;
            }
        },
        onHasExpiryChange(item) {
            if (!item.hasExpiry) {
                item.periodValue = 1;
                item.periodUnit = 'month';
                item.isInvalidator = false;
            }
        },
        onIsInvalidatorChange(event, item, openConfirm) {
            const checked = event.target.checked;

            if (!item.hasExpiry) {
                event.target.checked = false;
                return;
            }

            if (checked) {
                this.pendingInvalidatorItem = item;
                this.pendingInvalidatorValue = true;
                this.pendingInvalidatorCheckbox = event.target;
                event.target.checked = false;
                openConfirm();
            } else {
                item.isInvalidator = false;
            }
        },
        confirmIsInvalidator() {
            if (this.pendingInvalidatorItem) {
                this.pendingInvalidatorItem.isInvalidator = true;
                this.pendingInvalidatorItem = null;
                this.pendingInvalidatorValue = false;
            }
            if (this.pendingInvalidatorCheckbox) {
                this.pendingInvalidatorCheckbox.focus();
                this.pendingInvalidatorCheckbox = null;
            }
        },
        cancelIsInvalidator() {
            this.pendingInvalidatorItem = null;
            this.pendingInvalidatorValue = false;
            if (this.pendingInvalidatorCheckbox) {
                this.pendingInvalidatorCheckbox.focus();
                this.pendingInvalidatorCheckbox = null;
            }
        },
        onPeriodValueChange(item) {
            const value = parseInt(item.periodValue);
            item.periodValue = isNaN(value) || value < 1 ? 1 : value;
        },
        onPeriodUnitChange(item, option) {
            item.periodUnit = option.value;
        },
        buildLockedFieldsConfig () {
            const config = {};
            const allItems = [
                ...this.agents,
                ...this.spaces,
                ...this.taxonomiesAgents,
                ...this.taxonomiesSpaces
            ];

            allItems.forEach(item => {
                if (item.value) {
                    config[item.fieldKey] = {
                        hasExpiry: !!item.hasExpiry,
                        periodValue: item.hasExpiry ? (parseInt(item.periodValue) || 1) : null,
                        periodUnit: item.hasExpiry ? (item.periodUnit || 'month') : null,
                        isInvalidator: item.hasExpiry ? !!item.isInvalidator : false,
                    };
                }
            });

            this.entity.lockedFieldsConfig = config;
            // console.log('[seal-locked-field] buildLockedFieldsConfig assigned:', JSON.parse(JSON.stringify(config)));
        }
    }
});
