app.component('tab', {
    props: {
        cache: {
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
        const cached = Vue.ref(false)
        const hash = '#' + (!props.disabled ? props.slug : '')
        const isActive = Vue.ref(false)
        const tabsProvider = Vue.inject('tabsProvider')

        Vue.watch(
            () => tabsProvider.activeTab,
            () => {
                isActive.value = props.slug === tabsProvider.activeTab?.slug
                cached.value = cached.value || (isActive.value && props.cache)
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
            cached,
            hash,
            isActive,
        }
    },
    template: $TEMPLATES['tab']
});
