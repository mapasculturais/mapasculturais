<?php

namespace MapasCulturais\DoctrineEnumTypes;

use MyCLabs\Enum\Enum;

class ObjectType extends Enum {
    public const Agent = 'MapasCulturais\Entities\Agent';
    public const EvaluationMethodConfiguration = 'MapasCulturais\Entities\EvaluationMethodConfiguration';
    public const Event = 'MapasCulturais\Entities\Event';
    public const Notification = 'MapasCulturais\Entities\Notification';
    public const Opportunity = 'MapasCulturais\Entities\Opportunity';
    public const Project = 'MapasCulturais\Entities\Project';
    public const Registration = 'MapasCulturais\Entities\Registration';
    public const RegistrationFileConfiguration = 'MapasCulturais\Entities\RegistrationFileConfiguration';
    public const Request = 'MapasCulturais\Entities\Request';
    public const Seal = 'MapasCulturais\Entities\Seal';
    public const Space = 'MapasCulturais\Entities\Space';
    public const Subsite = 'MapasCulturais\Entities\Subsite';
}