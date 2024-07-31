app.component('colors-customizer', {
    template: $TEMPLATES['colors-customizer'],

    mounted() {
        if (!this.subsite.color_primary ) {
            this.subsite.color_primary = this.getCssValue('--mc-primary-500');
        }
        if (!this.subsite.color_secondary ) {
            this.subsite.color_secondary = this.getCssValue('--mc-secondary-500');
        }
        if (!this.subsite.color_seals ) {
            this.subsite.color_seals = this.getCssValue('--mc-seals-500');
        }
        if (!this.subsite.color_agents ) {
            this.subsite.color_agents = this.getCssValue('--mc-agents-500');
        }
        if (!this.subsite.color_events ) {
            this.subsite.color_events = this.getCssValue('--mc-events-500');
        }
        if (!this.subsite.color_opportunities ) {
            this.subsite.color_opportunities = this.getCssValue('--mc-opportunities-500');
        }
        if (!this.subsite.color_projects ) {
            this.subsite.color_projects = this.getCssValue('--mc-projects-500');
        }
        if (!this.subsite.color_spaces ) {
            this.subsite.color_spaces = this.getCssValue('--mc-spaces-500');
        }
    },

    data() {
        const subsite = $MAPAS.subsite;
        return {
            subsite,
        }
    },

    methods: {
        getCssValue(varName) {
            return getComputedStyle(document.documentElement).getPropertyValue(varName);
        }
    },
});
