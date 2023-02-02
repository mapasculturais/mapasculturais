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