app.component('opportunity-list', {
    template: $TEMPLATES['opportunity-list'],
    
    computed: {
        opportunities() {
            const opportunities = [];
            $MAPAS.opportunityList.opportunity.forEach(element => {
                const entity = new Entity('opportunity', element.id);
                entity.populate(element);
                opportunities.push(entity);
            });
            return opportunities;
        }
    }
});