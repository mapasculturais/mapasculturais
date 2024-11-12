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
            required: true,
        },

        titleModal: {
            type: String,
            default: '',
        },
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
            const nextValue = {
                categories: this.tempValue.categories.length > 0 ? this.tempValue.categories : undefined,
                proponentTypes: this.tempValue.proponentTypes.length > 0 ? this.tempValue.proponentTypes : undefined,
                ranges: this.tempValue.ranges.length > 0 ? this.tempValue.ranges : undefined,
            };
            this.$emit('update:modelValue', nextValue);
            this.selectedField = '';
            modal.close();
        },

        isFieldExcluded(field) {
            return this.excludeFields.includes(field);
        },

        resetFilters(value) {
            return {
                categories: [...(value.categories ?? [])],
                proponentTypes: [...(value.proponentTypes ?? [])],
                ranges: [...(value.ranges ?? [])],
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
