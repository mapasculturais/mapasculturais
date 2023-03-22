app.component('home-entities', {
    template: $TEMPLATES['home-entities'],
    setup() { 
        const text = Utils.getTexts('home-entities')
        return { text }
    },
    data() {
        return {
            agentText: __('agentText', 'home-entities'),
            projectText:__('projectText', 'home-entities'),
            opportunityText:__('opportunityText', 'home-entities'),
            spaceText:__('spaceText', 'home-entities'),
            eventText:__('eventText', 'home-entities')
        }
    },
    props: {
        
    },

    methods: {
         
    },
});
