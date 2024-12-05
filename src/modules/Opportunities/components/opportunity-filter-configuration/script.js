app.component('opportunity-filter-configuration', {
    template: $TEMPLATES['opportunity-filter-configuration'],

    emits: ['update:modelValue'],

    props: {
        excludeFields: {
            type: Array,
            default: () => [],
        },

        modelValue: {
            type: Object,
            default: () => {},
        },

        titleModal: {
            type: String,
            default: '',
        },
    },

    setup() {
        const text = Utils.getTexts('opportunity-filter-configuration');
        return { text };
    },

    data() {
        return {
            registrationCategories: $MAPAS.opportunityPhases[0].registrationCategories ?? [],
            registrationProponentTypes: $MAPAS.opportunityPhases[0].registrationProponentTypes ?? [],
            registrationRanges: $MAPAS.opportunityPhases[0].registrationRanges?.map(range => range.label) ?? [],
            selectedField: '',
            tempValue: this.resetFilters(this.modelValue),
        }
    },

    computed: {
        tags () {
            const tags = [];
            
            for (const type of Object.keys(this.tempValue)) {
                for (const value of this.tempValue[type]) {
                    tags.push(`${this.text(type)}: ${value}`);
                }
            }

            return tags.sort();
        },
    },

    watch: {
        modelValue: {
            handler(value) {
                this.tempValue = this.resetFilters(value);
            },
            immediate: true,
        },
    },

    methods: {
        cancelChanges(modal) {
            this.tempValue = this.resetFilters(this.modelValue);
            this.selectedField = '';
            modal.close();
        },

        confirmChanges(modal) {
            this.$emit('update:modelValue', this.normalizeFilters(this.tempValue));
            this.selectedField = '';
            modal.close();
        },

        dictTypes(reverse = false) {
            const dict = {};

            for (const type of ['categories', 'proponentTypes', 'ranges']) {
                if (reverse) {
                    dict[this.text(type)] = type;
                } else {
                    dict[type] = this.text(type);
                }
            }

            return dict;
        },

        isFieldExcluded(field) {
            return this.excludeFields.includes(field);
        },

        normalizeFilters(value) {
            return {
                categories: value.categories.length > 0 ? value.categories : undefined,
                proponentTypes: value.proponentTypes.length > 0 ? value.proponentTypes : undefined,
                ranges: value.ranges.length > 0 ? value.ranges : undefined,
            };
        },

        removeTag(tag) {
            const [displayKey, value] = tag.split(': ');
            const type = this.dictTypes(true)[displayKey];
            this.tempValue[type] = this.tempValue[type]?.filter((x) => x !== value);
            this.$emit('update:modelValue', this.normalizeFilters(this.tempValue));
        },

        resetFilters(value) {
            return {
                categories: [...(value?.categories ?? [])],
                proponentTypes: [...(value?.proponentTypes ?? [])],
                ranges: [...(value?.ranges ?? [])],
            };
        },

        showField(type) {
            switch (type) {
                case 'category':
                    return this.registrationCategories.length > 0;
                case 'proponentType':
                    return this.registrationProponentTypes.length > 0;
                case 'range':
                    return this.registrationRanges.length > 0;
            }
        },
    },
});
