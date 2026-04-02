app.component('oc-complementary', {
    template: $TEMPLATES['oc-complementary'],

    setup() {
        const text = Utils.getTexts('evaluation-actions')
        const globalState = useGlobalState();
        return { text, globalState }
    },

    props: {
        entity: {
            type: Entity,
            required: true
        }
    },
    computed: {
        tabGroups() {
            {
                return {
                    tabs: [
                        { label: 'Logotipo', isActive: true, submenu: [], ref: 'logo', useActions: true },
                        { label: 'Favicon SVG', isActive: false, submenu: [], ref: 'faviconSvg', useActions: false },
                        { label: 'Favicon PNG', isActive: false, submenu: [], ref: 'faviconPng', useActions: false },
                        { label: 'Imagem de compartilhamento', isActive: false, submenu: [], ref: 'share', useActions: false },
                        { label: 'Imagem de Email', isActive: false, submenu: [], ref: 'imgMail', useActions: false },
                    ],
                }
            }
        }
    },
    data() {
        let typeLogoDefinition = this.entity.typeLogoDefinition || 'default'
        this.globalState.useActions = typeLogoDefinition === 'default' ? true : false;
        return {
            typeLogoDefinition
        }
    },
    methods: {
        setTypeLogo() {
            this.typeLogoDefinition = this.entity.typeLogoDefinition;
        },
        reload() {
            window.location.reload();
        }
    }
});
