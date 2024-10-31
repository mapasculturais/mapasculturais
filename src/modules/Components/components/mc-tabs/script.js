app.component('mc-tabs', {
    template: $TEMPLATES['mc-tabs'],

    props: {
        draggable: {
            type: Array,
            default: null,
        },
        defaultTab: {
            type: String,
            default: null
        },
        syncHash: {
            type: Boolean,
            default: false
        },
        iconPosition:{
            type: String,
            default: "left"
        },
    },

    emits: ['changed', 'clicked', 'update:draggable'],

    setup(props, context) {
        const hasSlot = name => !!context.slots[name];
        const state = Vue.reactive({
            activeTab: '',
            tabs: []
        })

        Vue.provide('tabsProvider', state)

        const isActive = (tab) => {
            return tab.slug === state.activeTab?.slug
        }

        const findTab = (slug) => {
            return state.tabs.find(tab => tab.slug === slug)
        }

        const selectTab = (slug, event) => {
            event?.preventDefault()

            const nextTab = findTab(slug)
            if (!nextTab || nextTab.disabled) {
                return
            }

            if (state.activeTab?.slug === nextTab.slug) {
                context.emit('clicked', { tab: nextTab })
                return
            }

            if (props.syncHash) {
                window.location.hash = nextTab.hash
            }

            context.emit('clicked', { tab: nextTab })
            context.emit('changed', { tab: nextTab })
            state.activeTab = nextTab
        }

        const reorderTabs = ({ list, tabs }) => {
            state.tabs = tabs
            context.emit('update:draggable', list)
        }

        Vue.onMounted(() => {
            if (!state.tabs.length) {
                return
            }

            const hash = window.location.hash.slice(1)
            window.addEventListener('hashchange', () => selectTab(window.location.hash.slice(1)))

            if (props.syncHash && hash && findTab(hash)) {
                selectTab(hash)
            } else if (props.defaultTab && findTab(props.defaultTab)) {
                selectTab(props.defaultTab)
            } else {
                selectTab(state.tabs[0].slug)
            }
        })

        return {
            ...Vue.toRefs(state),
            isActive,
            findTab,
            reorderTabs,
            selectTab,
            hasSlot,
        }
    },
});
