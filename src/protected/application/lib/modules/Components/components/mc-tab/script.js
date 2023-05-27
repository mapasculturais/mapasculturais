app.component('mc-tab', {
    props: {
        cache: {
            type: [Boolean, Number],
            default: false
        },
        disabled: {
            type: Boolean,
            default: false
        },
        icon: {
            type: String,
            default: null,
        },
        label: {
            type: String,
            required: true
        },
        meta: {
            type: Object,
            default: () => ({})
        },
        slug: {
            type: String,
            required: true
        },
        classes: {
            type: [String, Array, Object],
            required: false
        },
    },
    setup(props) {
        const cached = Vue.ref(false)
        const hash = '#' + (!props.disabled ? props.slug : '')
        const isActive = Vue.ref(false)
        const tabsProvider = Vue.inject('tabsProvider')

        let timeout = null
        Vue.watch(
            () => tabsProvider.activeTab,
            () => {
                isActive.value = props.slug === tabsProvider.activeTab?.slug

                window.clearTimeout(timeout)
                if (props.cache) {
                    if (typeof props.cache === 'number') {
                        cached.value = true
                        timeout = window.setTimeout(() => {
                            cached.value = false
                        }, props.cache)
                    } else {
                        cached.value = !!(cached.value) || isActive.value
                    }
                }
            }
        )

        Vue.onBeforeMount(() => {
            tabsProvider.tabs.push({
                disabled: props.disabled,
                hash: hash,
                icon: props.icon,
                class: props.classes,
                label: props.label,
                meta: props.meta,
                slug: props.slug,
            })
        })

        return {
            cached,
            hash,
            isActive,
        }
    },
    template: $TEMPLATES['mc-tab']
});
