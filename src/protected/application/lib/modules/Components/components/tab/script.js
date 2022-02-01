app.component('tab', {
    props: {
        cached: {
            type: Boolean,
            default: false
        },
        disabled: {
            type: Boolean,
            default: false
        },
        label: {
            type: String,
            required: true
        },
        slug: {
            type: String,
            required: true
        },
    },
    setup(props) {
        const hash = '#' + (!props.disabled ? props.slug : '')
        const tabsProvider = Vue.inject('tabsProvider')

        const isActive = Vue.computed(() => props.slug === tabsProvider.activeTab?.slug)

        Vue.onBeforeMount(() => {
            tabsProvider.tabs.push({
                disabled: props.disabled,
                hash: hash,
                label: props.label,
                slug: props.slug,
            })
        })

        return {
            hash,
            isActive,
        }
    },
    template: $TEMPLATES['tab']
});
