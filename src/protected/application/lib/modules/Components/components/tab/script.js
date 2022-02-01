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
        const isActive = Vue.ref(false)
        const computedId = `tab-${props.slug}`
        const hash = '#' + (!props.disabled ? props.slug : '')
        const tabsProvider = Vue.inject('tabsProvider')

        Vue.watch(
            () => tabsProvider.activeTab,
            () => {
                isActive.value = props.slug === tabsProvider.activeTab?.slug
            }
        )

        Vue.onBeforeMount(() => {
            tabsProvider.tabs.push({
                disabled: props.disabled,
                hash: hash,
                label: props.label,
                slug: props.slug,
            })
        })

        return {
            computedId,
            hash,
            isActive,
        }
    },
    template: $TEMPLATES['tab']
});
