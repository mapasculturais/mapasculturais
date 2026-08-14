<?php
use MapasCulturais\i;
return [
    "module.Entities" => [
        'requiredAvatar' => [
            'MapasCulturais\Entities\Agent' => env('PROFILE_ENTITY_REQUIRED_AVATAR_AGENT', false),
            'MapasCulturais\Entities\Project' => env('PROFILE_ENTITY_REQUIRED_AVATAR_PROJECT', false),
            'MapasCulturais\Entities\Space' => env('PROFILE_ENTITY_REQUIRED_AVATAR_SPACE', false),
            'MapasCulturais\Entities\Event' => env('PROFILE_ENTITY_REQUIRED_AVATAR_EVENT', false),
            'MapasCulturais\Entities\Opportunity' => env('PROFILE_ENTITY_REQUIRED_AVATAR_OPPORTUNITY', false),    
        ],
    ],
];
