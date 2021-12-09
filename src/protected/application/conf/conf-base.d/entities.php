<?php 
use \MapasCulturais\i;

return [
    // entidades habilitadas
    'app.enabled.agents'        => !env('DISABLE_AGENTS', false),
    'app.enabled.spaces'        => !env('DISABLE_SPACES', false),
    'app.enabled.projects'      => !env('DISABLE_PROJECTS', false),
    'app.enabled.opportunities' => !env('DISABLE_OPPORTUNITIES', false),
    'app.enabled.events'        => !env('DISABLE_EVENTS', false),
    'app.enabled.subsite'       => !env('DISABLE_SUBSITES', false),
    'app.enabled.seals'         => !env('DISABLE_SEALS', false),
    'app.enabled.apps'          => !env('DISABLE_APPS', false),

    // cores padrão para os novos subsites
    'themes.brand-space'        => env('SUBSITE_COLOR_SPACE', '#e83f96'),
    'themes.brand-project'      => env('SUBSITE_COLOR_PROJECT', '#cc0033'),
    'themes.brand-opportunity'  => env('SUBSITE_COLOR_OPPORTUNITY', '#ffaa00'),
    'themes.brand-event'        => env('SUBSITE_COLOR_EVENT', '#b3b921'),
    'themes.brand-subsite'      => env('SUBSITE_COLOR_SUBSITE', '#ff5545'),
    'themes.brand-seal'         => env('SUBSITE_COLOR_SEAL', '#ff5545'),
    'themes.brand-agent'        => env('SUBSITE_COLOR_AGENT', '#1dabc6'),
    'themes.brand-intro'        => env('SUBSITE_COLOR_INTRO', '#1c5690'),
    'themes.brand-developer'    => env('SUBSITE_COLOR_DEVELOPER', '#9966cc'),

    'app.entityPropertiesLabels' => array(
        '@default' => array(
            'id' => i::__('Id'),
            'name' => i::__('Nome'),
            '_type' => i::__('Tipo'),
            'createTimestamp' => i::__('Data de Criação'),
            'updateTimestamp' => i::__('Data de Atualização'),
            'shortDescription' => i::__('Descrição Curta'),
            'longDescription' => i::__('Descrição Longa'),
            'certificateText' => i::__('Conteúdo da Impressão do Certificado'),
            'validPeriod'	=> i::__('Período de Validade'),
            'status' => i::__('Status'),
            'registrationFrom' => i::__('Data de início das inscrições'), 
            'registrationTo' => i::__('Data final das inscrições')
        ),

        'MapasCulturais\Entities\Agent' => array(
            'publicLocation' => i::__('Localização publicada'),
            'location' => i::__('Localização'),
            'userId' => i::__('ID usuário'),
        )
    ),

];