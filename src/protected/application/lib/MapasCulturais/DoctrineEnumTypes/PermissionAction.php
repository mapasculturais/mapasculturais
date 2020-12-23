<?php

namespace MapasCulturais\DoctrineEnumTypes;

use MyCLabs\Enum\Enum;

class PermissionAction extends Enum {
    public const approve = 'approve';
    public const archive = 'archive';
    public const changeOwner = 'changeOwner';
    public const changeStatus = 'changeStatus';
    public const control = '@control';
    public const create = 'create';
    public const createAgentRelation = 'createAgentRelation';
    public const createAgentRelationWithControl = 'createAgentRelationWithControl';
    public const createEvents = 'createEvents';
    public const createSealRelation = 'createSealRelation';
    public const createSpaceRelation = 'createSpaceRelation';
    public const destroy = 'destroy';
    public const evaluate = 'evaluate';
    public const evaluateRegistrations = 'evaluateRegistrations';
    public const modify = 'modify';
    public const modifyRegistrationFields = 'modifyRegistrationFields';
    public const modifyValuers = 'modifyValuers';
    public const publish = 'publish';
    public const publishRegistrations = 'publishRegistrations';
    public const register = 'register';
    public const reject = 'reject';
    public const remove = 'remove';
    public const removeAgentRelation = 'removeAgentRelation';
    public const removeAgentRelationWithControl = 'removeAgentRelationWithControl';
    public const removeSealRelation = 'removeSealRelation';
    public const removeSpaceRelation = 'removeSpaceRelation';
    public const reopenValuerEvaluations = 'reopenValuerEvaluations';
    public const requestEventRelation = 'requestEventRelation';
    public const send = 'send';
    public const sendUserEvaluations = 'sendUserEvaluations';
    public const unpublish = 'unpublish';
    public const view = 'view';
    public const viewConsolidatedResult = 'viewConsolidatedResult';
    public const viewEvaluations = 'viewEvaluations';
    public const viewPrivateData = 'viewPrivateData';
    public const viewPrivateFiles = 'viewPrivateFiles';
    public const viewRegistrations = 'viewRegistrations';
    public const viewUserEvaluation = 'viewUserEvaluation';
}