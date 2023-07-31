<?php
$qa_params = [
    'type' => 'EQ(1)',
];

if ($app->user->is('admin')) {
    $qa_params['user'] = 'EQ(@me)';
    $qa_params['@permissions'] = 'view';
} else {
    $qa_params['@permissions'] = '@control';
    
}

$qa = new MapasCulturais\ApiQuery(MapasCulturais\Entities\Agent::class, $qa_params);

$q = new MapasCulturais\ApiQuery(MapasCulturais\Entities\Registration::class, [
    'status' => 'EQ(0)',
    '@permissions' => 'view',
]);

$q->addFilterByApiQuery($qa, 'id', 'owner');

$result = $q->getCountResult();

$this->jsObject['config']['panelRegistrationTabs']['totalDrafts'] = $result;