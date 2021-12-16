app.component('tab', {
    props: {
        panelClass: {
            type: String,
            default: 'tabs-component-panel'
        },
        navClass: {
            type: String,
            value: ''
        },
        id: {
            type: String,
            default: null
        },
        name: {
            type: String,
            required: true
        },
        prefix: {
            type: String,
            default: ''
        },
        suffix: {
            type: String,
            default: ''
        },
        isDisabled: {
            type: Boolean,
            default: false
        },
        cacheTls: {
            type: Number,
            default: 60000
        }
    },
    setup(props) {
        const isActive = Vue.ref(false)
        const cached = Vue.ref(false)
        const tabsProvider = Vue.inject('tabsProvider')
        const header = props.prefix + props.name + props.suffix
        const computedId = props.id ? props.id : props.name.toLowerCase().replace(/ /g, '-')
        const hash = '#' + (!props.isDisabled ? computedId : '')
        let timeout;
        Vue.watch(
            () => tabsProvider.activeTabHash,
            () => {
                isActive.value = hash === tabsProvider.activeTabHash
                clearTimeout(timeout);
                if(isActive.value) {
                    cached.value = true
                } else {
                    timeout = setTimeout(() => {
                        cached.value = false
                    }, props.cacheTls)

                }
            }
        )
        Vue.onBeforeMount(() => {
            tabsProvider.tabs.push({
                name: props.name,
                header: header,
                isDisabled: props.isDisabled,
                hash: hash,
                index: tabsProvider.tabs.length,
                navClass: props.navClass
            })
        })

        return {
            header,
            computedId,
            hash,
            isActive,
            cached
        }
    },
    template: $TEMPLATES['tab']
});
