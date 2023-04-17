<?php

namespace MapasCulturais\DoctrineEnumTypes;

use MapasCulturais\DoctrineEnumType;

class PermissionAction extends DoctrineEnumType
{
    public static function getTypeName(): string
    {
        return 'permission_action';
    }

    protected static function getKeysValues(): array
    {
        return [
            'approve' => 'approve',
            'archive' => 'archive',
            'changeOwner' => 'changeOwner',
            'changeStatus' => 'changeStatus',
            'changeType' => 'changeType',
            'changeUserProfile' => 'changeUserProfile',
            'control' => '@control',
            'create' => 'create',
            'createAgentRelation' => 'createAgentRelation',
            'createAgentRelationWithControl' => 'createAgentRelationWithControl',
            'createEvents' => 'createEvents',
            'createSealRelation' => 'createSealRelation',
            'createSpaceRelation' => 'createSpaceRelation',
            'destroy' => 'destroy',
            'evaluate' => 'evaluate',
            'evaluateOnTime' => 'evaluateOnTime',
            'evaluateRegistrations' => 'evaluateRegistrations',
            'modify' => 'modify',
            'modifyRegistrationFields' => 'modifyRegistrationFields',
            'modifyValuers' => 'modifyValuers',
            'post' => 'post',
            'publish' => 'publish',
            'publishRegistrations' => 'publishRegistrations',
            'register' => 'register',
            'reject' => 'reject',
            'remove' => 'remove',
            'removeAgentRelation' => 'removeAgentRelation',
            'removeAgentRelationWithControl' => 'removeAgentRelationWithControl',
            'removeSealRelation' => 'removeSealRelation',
            'removeSpaceRelation' => 'removeSpaceRelation',
            'reopenValuerEvaluations' => 'reopenValuerEvaluations',
            'requestEventRelation' => 'requestEventRelation',
            'send' => 'send',
            'sendUserEvaluations' => 'sendUserEvaluations',
            'unpublish' => 'unpublish',
            'view' => 'view',
            'viewConsolidatedResult' => 'viewConsolidatedResult',
            'viewEvaluations' => 'viewEvaluations',
            'viewPrivateData' => 'viewPrivateData',
            'viewPrivateFiles' => 'viewPrivateFiles',
            'viewRegistrations' => 'viewRegistrations',
            'viewUserEvaluation' => 'viewUserEvaluation',
        ];
    }
}
