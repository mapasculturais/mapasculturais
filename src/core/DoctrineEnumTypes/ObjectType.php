<?php

namespace MapasCulturais\DoctrineEnumTypes;

use MapasCulturais\DoctrineEnumType;

/**
 * Enum para tipos de objetos no sistema
 * 
 * Este enum mapeia nomes amigáveis para classes de entidades do sistema,
 * permitindo referenciar tipos de objetos de forma consistente no banco de dados.
 * 
 * @package MapasCulturais\DoctrineEnumTypes
 */
class ObjectType extends DoctrineEnumType
{
    /**
     * Retorna o nome do tipo usado no banco de dados
     * 
     * @return string Nome do tipo no banco de dados
     */
    public static function getTypeName(): string
    {
        return 'object_type';
    }

    /**
     * Retorna um array com as chaves e valores do enum
     * 
     * As chaves são nomes amigáveis e os valores são classes de entidade correspondentes.
     * 
     * @return array Array associativo com chaves e valores do enum
     */
    protected static function getKeysValues(): array
    {
        return [
            'Agent' => 'MapasCulturais\Entities\Agent',
            'ChatMessage' => 'MapasCulturais\Entities\ChatMessage',
            'ChatThread' => 'MapasCulturais\Entities\ChatThread',
            'EvaluationMethodConfiguration' => 'MapasCulturais\Entities\EvaluationMethodConfiguration',
            'Event' => 'MapasCulturais\Entities\Event',
            'Notification' => 'MapasCulturais\Entities\Notification',
            'Opportunity' => 'MapasCulturais\Entities\Opportunity',
            'Project' => 'MapasCulturais\Entities\Project',
            'Registration' => 'MapasCulturais\Entities\Registration',
            'RegistrationEvaluation' => 'MapasCulturais\Entities\RegistrationEvaluation',
            'RegistrationFileConfiguration' => 'MapasCulturais\Entities\RegistrationFileConfiguration',
            'Request' => 'MapasCulturais\Entities\Request',
            'Seal' => 'MapasCulturais\Entities\Seal',
            'Space' => 'MapasCulturais\Entities\Space',
            'Subsite' => 'MapasCulturais\Entities\Subsite',
            'User' => 'MapasCulturais\Entities\User',
            'Delivery' => 'OpportunityWorkplan\Entities\Delivery',
        ];
    }
}
