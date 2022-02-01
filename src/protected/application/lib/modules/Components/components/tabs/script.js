app.component('tabs', {
    template: $TEMPLATES['tabs'],

    props: {
        defaultTab: {
            type: String,
            default: null
        },
        useUrlFragment: {
            type: Boolean,
            default: true
        },
    },

    emits: ['changed', 'clicked'],

    setup(props, context) {
        const state = Vue.reactive({
            activeTab: '',
            tabs: []
        })

        Vue.provide('tabsProvider', state)

        const selectTab = (slug, event) => {
            event?.preventDefault()

            const nextTab = findTab(slug)
            if (!nextTab || nextTab.disabled) {
                return
            }

            if (state.activeTab.slug === nextTab.slug) {
                context.emit('clicked', { tab: nextTab })
                return
            }

            if (props.useUrlFragment) {
                window.location.hash = nextTab.hash
            }

            context.emit('clicked', { tab: nextTab })
            context.emit('changed', { tab: nextTab })
            state.activeTab = nextTab
        }

        const findTab = (slug) => {
            return state.tabs.find(tab => tab.slug === slug)
        }

        Vue.onMounted(() => {
            if (!state.tabs.length) {
                return
            }

            const hash = window.location.hash.slice(1)
            window.addEventListener('hashchange', () => selectTab(window.location.hash.slice(1)))

            if (props.useUrlFragment && hash && findTab(hash)) {
                selectTab(hash)
            } else if (props.defaultTab && findTab(props.defaultTab)) {
                selectTab(props.defaultTab)
            } else {
                selectTab(state.tabs[0].slug)
            }
        })

        return {
            ...Vue.toRefs(state),
            findTab,
            selectTab
        }
    }
});
