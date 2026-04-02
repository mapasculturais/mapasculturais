app.component('oc-tabs', {
    template: $TEMPLATES['oc-tabs'],

    mounted() {
        window.addEventListener('stepActive', this.setStepActive);
    },
    props: {
        entity: {
            type: Entity,
            required: true
        },
        groups: {
            type: Object,
            default: {},
            required: true
        },
        initialGroup: {
            type: String,
            default: ""
        },
        sotaregeRef: {
            type: String,
            required: true
        }
    },
    watch: {
        'step'(_new, _old) {
            if (_new != _old) {
                this.changeStep(_new)
            }
        }
    },
    data() {
        let groups = this.groups[this.initialGroup];
        let tabs = JSON.parse(localStorage.getItem("octabs"));
        let oldActive = null;
        if(tabs) {
            groups.forEach(element => {
                if(element.isActive) {
                    oldActive = element.ref
                }
                element.isActive = false;
            });
    
            let setActive = false;
            groups.forEach(element => {
                if((tabs[this.sotaregeRef] && tabs[this.sotaregeRef] == element.ref)) {
                    element.isActive = true;
                    setActive = true;
                }
            });

            if(!setActive) {
                groups.forEach(element => {
                    if(oldActive == element.ref) {
                        element.isActive = true;
                    }
                });
            }
        } 
       
        return {
            activeTab: this.groups[this.initialGroup],
            actioveOption: null
        }
    },

    methods: {
        changeStep(group) {
            this.activeTab = [];
            this.activeTab = this.groups[group];
        },

        changeOption(ref) {
            let data = localStorage.getItem("octabs") ? JSON.parse(localStorage.getItem("octabs")) : {};
            this.activeTab.forEach(item => {
                item.isActive = false;
                this.actioveOption = null;
                if (item.ref === ref) {
                    data[this.sotaregeRef] =  ref;
                    localStorage.setItem("octabs", JSON.stringify(data));
                    window.dispatchEvent(new CustomEvent('useActions', { detail: { useActions: item.useActions } }));
                    item.isActive = true;
                    this.actioveOption = ref
                }
            });
        },

        setStepActive(data) {
            let step = data.detail.stepActive;
            this.activeTab = this.groups[step]
        }
    }
});
