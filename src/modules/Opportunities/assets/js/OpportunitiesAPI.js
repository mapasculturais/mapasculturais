class ReportsAPI {
    constructor() {
        this.api = new API('reports');
    }

    _withFilters(url, { status } = {}) {
        url.searchParams.set('status', status || 'all');
        return url;
    }

    getStaticCharts(opportunityId, filters = {}) {
        const url = this._withFilters(this.api.createUrl('staticCharts', { opportunity_id: opportunityId }), filters);
        return this.api.GET(url).then(r => r.json());
    }

    getReportFields(opportunityId) {
        const url = this.api.createUrl('reportFields', { opportunity_id: opportunityId });
        return this.api.GET(url).then(r => r.json());
    }

    getGraphics(opportunityId, filters = {}) {
        const url = this._withFilters(this.api.createUrl('graphics', { opportunity_id: opportunityId }), filters);
        return this.api.GET(url).then(r => r.json());
    }

    previewGraphic(opportunityId, reportData, filters = {}) {
        const url = this._withFilters(this.api.createUrl('graphicPreview', { opportunity_id: opportunityId }), filters);
        url.searchParams.set('reportData', JSON.stringify(reportData));
        return this.api.GET(url).then(r => r.json());
    }

    saveGraphic(payload) {
        const url = this.api.createUrl('saveGraphic');
        return this.api.POST(url, payload).then(r => r.json());
    }

    deleteGraphic(opportunityId, graphicId) {
        const url = this.api.createUrl('deleteGraphic');
        return this.api.DELETE(url, { opportunity_id: opportunityId, graphicId }).then(r => r.json());
    }

    csvExportUrl(action, opportunityId, filters = {}, extraParams = {}) {
        const url = this._withFilters(this.api.createUrl(action, { opportunity_id: opportunityId, ...extraParams }), filters);
        return url.toString();
    }
}

/**
 * Campos computados no simplify/jsonSerialize do EMC que não estão em $PROPERTIES
 * e por isso são ignorados por Entity.populate().
 */
const EVALUATION_METHOD_CONFIGURATION_VIRTUAL_FIELDS = ['canEditSealConfig'];

function applyEvaluationMethodConfigurationVirtualFields(instance, item) {
    if (item['@entityType'] !== 'evaluationmethodconfiguration') {
        return;
    }

    for (const field of EVALUATION_METHOD_CONFIGURATION_VIRTUAL_FIELDS) {
        if (Object.prototype.hasOwnProperty.call(item, field)) {
            instance[field] = item[field];
        }
    }
}

function createOpportunityPhaseRawProcessor(APIs) {
    return (item) => {
        const entityType = item['@entityType'];
        const api = APIs[entityType];
        const instance = api.getEntityInstance(item.id);
        instance.populate(item);
        applyEvaluationMethodConfigurationVirtualFields(instance, item);
        return instance;
    };
}

class OpportunitiesAPI {
    getPhases(opportunityId) {
        const APIs = {
            opportunity: new API('opportunity'), 
            evaluationmethodconfiguration: new API('evaluationmethodconfiguration'), 
        };

        const rawProcessor = createOpportunityPhaseRawProcessor(APIs);

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

if ($MAPAS.opportunityPhases) {
    const APIs = {
        opportunity: new API('opportunity'), 
        evaluationmethodconfiguration: new API('evaluationmethodconfiguration'), 
    };

    const rawProcessor = createOpportunityPhaseRawProcessor(APIs);

    $MAPAS.opportunityPhases = $MAPAS.opportunityPhases.map(rawProcessor);

    $MAPAS.opportunityPhases[0].isFirstPhase = true;
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
