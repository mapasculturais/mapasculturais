<?php
/* Get agents from logged user */
$queryAgents = new MapasCulturais\ApiQuery(MapasCulturais\Entities\Agent::class, [
    '@select' => 'id,name,files.avatar', 
    '@permissions' => '@control', 
    '@limit' => '2',
    'type' => 'EQ(1)',
]);
$this->jsObject['config']['opportunitySubscription']['agents'] = $queryAgents->getFindResult();

/* Get all registrations */
$totalRegistrations = $this->controller->requestedEntity->totalRegistrations;
$this->jsObject['config']['opportunitySubscription']['totalRegistrations'] = $totalRegistrations;

/* Get registrations per user */
$queryUser_params = [
    'type' => 'EQ(1)',
];
if ($app->user->is('admin')) {
    $queryUser_params['user'] = 'EQ(@me)';
} else {
    $queryUser_params['@permissions'] = '@control';
}
$queryUser = new MapasCulturais\ApiQuery(MapasCulturais\Entities\Agent::class, $queryUser_params);
$queryRegistration = new MapasCulturais\ApiQuery(MapasCulturais\Entities\Registration::class, [
    '@select' => 'id,number,category,status,createTimestamp,owner.{id,name,files.avatar,}',
    'opportunity' => "EQ({$this->controller->requestedEntity->id})",
    'status' => 'GTE(0)',
    '@permissions' => 'view',
]);
$queryRegistration->addFilterByApiQuery($queryUser, 'id', 'owner');
$totalRegistrationsPerUser = $queryRegistration->getCountResult();
$this->jsObject['config']['opportunitySubscription']['totalRegistrationsPerUser'] = $totalRegistrationsPerUser;
