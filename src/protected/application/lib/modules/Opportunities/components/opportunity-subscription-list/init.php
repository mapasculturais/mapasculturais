<?php
$qa_params = [
    '@select' => 'id,name,files.avatar', 
    'type' => 'EQ(1)',
];

if ($app->user->is('admin')) {
    $qa_params['user'] = 'EQ(@me)';
} else {
    $qa_params['@permissions'] = '@control';
}

$qa = new MapasCulturais\ApiQuery(MapasCulturais\Entities\Agent::class, $qa_params);

$q = new MapasCulturais\ApiQuery(MapasCulturais\Entities\Registration::class, [
    '@select' => 'id,number,category,status,owner.{id,name,files.avatar}', 
    'status' => 'GTE(0)',
    '@permissions' => 'view',
]);

$q->addFilterByApiQuery($qa, 'id', 'owner');


$result = $q->getFindResult();

$this->jsObject['config']['opportunitySubscriptionList']['registrations'] = $result;