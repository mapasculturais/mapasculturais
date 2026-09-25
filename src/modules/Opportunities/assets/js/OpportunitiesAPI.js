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