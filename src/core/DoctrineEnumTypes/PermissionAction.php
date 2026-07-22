<?php

namespace MapasCulturais\DoctrineEnumTypes;

use MapasCulturais\DoctrineEnumType;

/**
 * Enum para ações de permissão no sistema
 * 
 * Este enum define todas as ações que podem ser realizadas sobre entidades,
 * usadas no sistema de controle de permissões do Mapas Culturais.
 * 
 * @package MapasCulturais\DoctrineEnumTypes
 */
class PermissionAction extends DoctrineEnumType
{
    /**
     * Retorna o nome do tipo usado no banco de dados
     * 
     * @return string Nome do tipo no banco de dados
     */
    public static function getTypeName(): string
    {
        return 'permission_action';
    }

    /**
     * Retorna um array com as chaves e valores do enum
     * 
     * As chaves e valores são idênticos, representando os nomes das ações de permissão.
     * 
     * @return array Array associativo com chaves e valores do enum
     */
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
            'deleteAccount' => 'deleteAccount',
            'destroy' => 'destroy',
            'evaluate' => 'evaluate',
            'evaluateOnTime' => 'evaluateOnTime',
            'evaluateRegistrations' => 'evaluateRegistrations',
            'manageEvaluationCommittee' => 'manageEvaluationCommittee',
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
            'reopenValuerEvaluations' => 'reopenValuerEvaluations',  //deprecated
            'requestEventRelation' => 'requestEventRelation',
            'send' => 'send',
            'sendUserEvaluations' => 'sendUserEvaluations',
            'sendEditableFields' => 'sendEditableFields',
            'support' => 'support',
            'unpublish' => 'unpublish',
            'unarchive' => 'unarchive',
            'view' => 'view',
            'viewConsolidatedResult' => 'viewConsolidatedResult',
            'viewEvaluations' => 'viewEvaluations',
            'viewPrivateData' => 'viewPrivateData',
            'viewPrivateFiles' => 'viewPrivateFiles',
            'viewRegistrations' => 'viewRegistrations',
            'viewUserEvaluation' => 'viewUserEvaluation',
            'modifyReadonlyData' => 'modifyReadonlyData',
            'sendEditableFields' => 'sendEditableFields',
            'applySeal' => 'applySeal'
        ];
    }
}
