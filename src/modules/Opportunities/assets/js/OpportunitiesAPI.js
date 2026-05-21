class OpportunitiesAPI {
    getPhases(opportunityId) {
        const APIs = {
            opportunity: new API('opportunity'), 
            evaluationmethodconfiguration: new API('evaluationmethodconfiguration'), 
        }

        const rawProcessor = (item) => {
            const entityType = item['@entityType'];
            const api = APIs[entityType];
            const instance = api.getEntityInstance(item.id);
            instance.populate(item);
            return instance;
        };

        return APIs['opportunity'].fetch('phases', {'@opportunity': opportunityId}, {raw: true, rawProcessor});
    }
}   

if ($MAPAS.opportunity) {
    let api = new API('opportunity');
    let opportunity = api.getEntityInstance($MAPAS.opportunity.id);
    opportunity.populate($MAPAS.opportunity);

    $MAPAS.opportunity = opportunity;

    if ($MAPAS.requestedEntity.opportunity && opportunity.id == $MAPAS.requestedEntity.opportunity) {
        $MAPAS.requestedEntity.opportunity = opportunity;
    } 
}

const processOpportunityPhases = (phases) => {
    const APIs = {
        opportunity: new API('opportunity'),
        evaluationmethodconfiguration: new API('evaluationmethodconfiguration'),
    };

    const rawProcessor = (item) => {
        const entityType = item['@entityType'];
        const api = APIs[entityType];
        const instance = api.getEntityInstance(item.id);
        instance.populate(item);
        return instance;
    };

    const processed = phases.map(rawProcessor);
    if (processed[0]) {
        processed[0].isFirstPhase = true;
    }
    return processed;
};

if ($MAPAS.opportunityPhases) {
    $MAPAS.opportunityPhases = processOpportunityPhases($MAPAS.opportunityPhases);
}

if ($MAPAS.evaluationPhases) {
    $MAPAS.evaluationPhases = processOpportunityPhases($MAPAS.evaluationPhases);
}

if ($MAPAS.registrationPhases) {
    const api = new API('registration');

    const rawProcessor = (item) => {
        const instance = api.getEntityInstance(item.id);
        instance.populate(item);
        return instance;
    };

    for(let key in $MAPAS.registrationPhases) {
        $MAPAS.registrationPhases[key] = rawProcessor($MAPAS.registrationPhases[key]);
    }
}