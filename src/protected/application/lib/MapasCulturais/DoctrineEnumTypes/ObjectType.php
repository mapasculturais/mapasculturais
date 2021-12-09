<?php

namespace MapasCulturais\DoctrineEnumTypes;

use MapasCulturais\DoctrineEnumType;

class ObjectType extends DoctrineEnumType
{
    public static function getTypeName(): string
    {
        return 'object_type';
    }

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
            'Subsite' => 'MapasCulturais\Entities\Subsite'
        ];
    }
}
